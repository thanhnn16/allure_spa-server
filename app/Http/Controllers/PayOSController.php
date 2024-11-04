<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\PaymentHistory;
use PayOS\PayOS;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PayOSController extends Controller
{
    protected $payOS;

    public function __construct(PayOS $payOS)
    {
        $this->payOS = $payOS;
    }

    private function createPaymentLink(array $paymentData)
    {
        try {
            // Log payment request
            Log::info('PayOS Request:', $paymentData);

            // Call PayOS API
            $response = $this->payOS->createPaymentLink($paymentData);

            // Log PayOS response
            Log::info('PayOS Response:', $response);

            if (isset($response['checkoutUrl'])) {
                return [
                    'success' => true,
                    'checkoutUrl' => $response['checkoutUrl'],
                    'orderCode' => $paymentData['orderCode'],
                    'qrCode' => $response['qrCode'] ?? null,
                ];
            }

            return [
                'success' => false,
                'message' => 'Không thể tạo link thanh toán',
                'error' => $response['desc'] ?? null
            ];
        } catch (\Exception $e) {
            Log::error('PayOS Error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Lỗi xử lý thanh toán: ' . $e->getMessage()
            ];
        }
    }

    public function testPayment(Request $request)
    {
        // Generate orderCode as timestamp + random string
        $orderCode = 'TEST_' . time() . '_' . substr(md5(uniqid()), 0, 8);

        // Validate input data
        $amount = intval($request->amount ?? 2000);
        $description = $request->description ?? 'Test payment';

        // Prepare payment data according to PayOS API docs
        $paymentData = [
            'orderCode' => $orderCode,
            'amount' => $amount,
            'description' => $description,
            'returnUrl' => $request->returnUrl,
            'cancelUrl' => $request->cancelUrl,
            'buyerName' => $request->buyerName ?? null,
            'buyerEmail' => $request->buyerEmail ?? null,
            'buyerPhone' => $request->buyerPhone ?? null,
            'buyerAddress' => $request->buyerAddress ?? null,
        ];

        $result = $this->createPaymentLink($paymentData);
        return response()->json($result, $result['success'] ? 200 : 400);
    }

    public function processPayment(Request $request)
    {
        try {
            $invoice = Invoice::findOrFail($request->invoice_id);
            
            if (!$invoice->isPending() && !$invoice->isPartiallyPaid()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Hóa đơn không hợp lệ để thanh toán'
                ], 400);
            }

            $orderCode = 'INV_' . $invoice->id . '_' . time();
            $amount = intval($invoice->remaining_amount * 100); // Convert to smallest currency unit

            $paymentData = [
                'orderCode' => $orderCode,
                'amount' => $amount,
                'description' => "Thanh toán hóa đơn #" . $invoice->id,
                'returnUrl' => $request->returnUrl,
                'cancelUrl' => $request->cancelUrl,
                'buyerName' => $invoice->user->full_name ?? null,
                'buyerEmail' => $invoice->user->email ?? null,
                'buyerPhone' => $invoice->user->phone ?? null,
                'buyerAddress' => $invoice->user->address ?? null,
            ];

            $result = $this->createPaymentLink($paymentData);
            
            if ($result['success']) {
                // Store payment information
                PaymentHistory::create([
                    'invoice_id' => $invoice->id,
                    'amount' => $amount / 100, // Convert back to normal currency
                    'payment_method' => 'payos',
                    'status' => 'pending',
                    'transaction_code' => $orderCode,
                ]);
            }

            return response()->json($result, $result['success'] ? 200 : 400);
        } catch (\Exception $e) {
            Log::error('PayOS Process Payment Error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Lỗi xử lý thanh toán: ' . $e->getMessage()
            ], 500);
        }
    }

    public function verifyPayment(Request $request)
    {
        try {
            $orderCode = $request->orderCode;
            
            if (!$orderCode) {
                throw new \Exception('Thiếu mã đơn hàng');
            }

            // Verify payment status with PayOS
            $response = $this->payOS->getPaymentLinkInformation($orderCode);
            Log::info('PayOS Verification Response:', $response);

            if ($response && $response['status'] === 'PAID') {
                // Handle successful payment
                if (str_starts_with($orderCode, 'INV_')) {
                    // Update invoice payment
                    $this->handleInvoicePayment($orderCode, $response);
                }

                return response()->json([
                    'success' => true,
                    'data' => [
                        'status' => 'PAID',
                        'amount' => $response['amount'],
                        'orderCode' => $orderCode,
                        'paymentTime' => $response['createdAt'] ?? null,
                    ]
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Thanh toán chưa hoàn tất',
                'data' => [
                    'status' => $response['status'] ?? 'PENDING'
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('PayOS Verification Error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    private function handleInvoicePayment($orderCode, $paymentResponse)
    {
        // Extract invoice ID from orderCode
        preg_match('/INV_([^_]+)_/', $orderCode, $matches);
        $invoiceId = $matches[1] ?? null;

        if (!$invoiceId) {
            Log::error('Invalid order code format:', ['orderCode' => $orderCode]);
            return;
        }

        try {
            $payment = PaymentHistory::where('transaction_code', $orderCode)->first();
            if ($payment) {
                $payment->update([
                    'status' => 'completed',
                    'payment_details' => json_encode($paymentResponse)
                ]);

                $invoice = Invoice::find($invoiceId);
                if ($invoice) {
                    $invoice->paid_amount += $payment->amount;
                    $invoice->remaining_amount = $invoice->calculateRemainingAmount();
                    $invoice->status = $invoice->remaining_amount > 0 ? 
                        Invoice::STATUS_PARTIALLY_PAID : 
                        Invoice::STATUS_PAID;
                    $invoice->save();
                }
            }
        } catch (\Exception $e) {
            Log::error('Error handling invoice payment:', [
                'error' => $e->getMessage(),
                'orderCode' => $orderCode
            ]);
        }
    }
}
