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

    protected function createPaymentLink(array $paymentData)
    {
        try {
            Log::info('PayOS Request:', $paymentData);

            $response = $this->payOS->createPaymentLink($paymentData);
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
        $orderCode = intval(substr(time() . rand(10, 99), -8));
        $amount = intval($request->amount ?? 2000);

        $paymentData = [
            'orderCode' => $orderCode,
            'amount' => $amount,
            'description' => "HD" . $orderCode, // Simplified description
            'returnUrl' => $request->returnUrl,
            'cancelUrl' => $request->cancelUrl,
        ];

        // Add optional buyer info if provided
        if ($request->buyerName) {
            $paymentData['buyerName'] = substr($request->buyerName, 0, 255);
        }
        if ($request->buyerEmail) {
            $paymentData['buyerEmail'] = substr($request->buyerEmail, 0, 255);
        }
        if ($request->buyerPhone) {
            $paymentData['buyerPhone'] = substr($request->buyerPhone, 0, 20);
        }
        if ($request->buyerAddress) {
            $paymentData['buyerAddress'] = substr($request->buyerAddress, 0, 255);
        }

        $result = $this->createPaymentLink($paymentData);
        return response()->json($result, $result['success'] ? 200 : 400);
    }

    public function processPayment(Request $request)
    {
        try {
            $invoice = Invoice::find($request->invoice_id);

            if (!$invoice) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy hóa đơn'
                ], 404);
            }

            if (!$invoice->isPending() && !$invoice->isPartiallyPaid()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Hóa đơn không hợp lệ để thanh toán'
                ], 400);
            }

            $orderCode = intval($invoice->id . time() . rand(100, 999));
            $amount = intval($invoice->remaining_amount * 100);

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
                'items' => $this->formatOrderItems($invoice->order->items),
            ];

            $result = $this->createPaymentLink($paymentData);

            if ($result['success']) {
                PaymentHistory::create([
                    'invoice_id' => $invoice->id,
                    'amount' => $amount / 100,
                    'payment_method' => 'payos',
                    'status' => 'pending',
                    'transaction_code' => $orderCode,
                ]);
            }

            return response()->json($result, $result['success'] ? 200 : 400);
        } catch (\Exception $e) {
            Log::error('PayOS Process Payment Error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'invoice_id' => $request->invoice_id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Lỗi xử lý thanh toán: ' . $e->getMessage()
            ], 500);
        }
    }

    private function formatOrderItems($orderItems)
    {
        return $orderItems->map(function ($item) {
            return [
                'name' => $item->item_type === 'product' ?
                    $item->product->name :
                    $item->service->name,
                'quantity' => $item->quantity,
                'price' => $item->price
            ];
        })->toArray();
    }

    public function verifyPayment(Request $request)
    {
        try {
            $orderCode = $request->orderCode;
            if (!$orderCode) {
                throw new \Exception('Thiếu mã đơn hàng');
            }

            $response = $this->payOS->getPaymentLinkInformation($orderCode);
            Log::info('PayOS Verification Response:', $response);

            if ($response && isset($response['status'])) {
                $paymentStatus = $response['status'];
                $amount = isset($response['amount']) ? $response['amount'] / 100 : 0; // Convert from smallest unit

                // Extract invoice ID from orderCode if it exists
                preg_match('/^(\d+)/', $orderCode, $matches);
                $invoiceId = $matches[1] ?? null;

                if ($paymentStatus === 'PAID' && $invoiceId) {
                    $payment = PaymentHistory::where('transaction_code', $orderCode)->first();
                    if ($payment) {
                        $payment->update([
                            'status' => 'completed',
                            'payment_details' => json_encode($response)
                        ]);

                        $invoice = Invoice::find($invoiceId);
                        if ($invoice) {
                            $invoice->paid_amount += $amount;
                            $invoice->remaining_amount = $invoice->calculateRemainingAmount();
                            $invoice->status = $invoice->remaining_amount > 0 ?
                                'partially_paid' : 'paid';
                            $invoice->save();
                        }
                    }
                }

                return response()->json([
                    'success' => true,
                    'data' => [
                        'status' => $paymentStatus,
                        'amount' => $amount,
                        'orderCode' => $orderCode,
                        'paymentTime' => $response['createdAt'] ?? null,
                    ]
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Không thể xác thực thanh toán'
            ], 400);
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
        preg_match('/^(\d+)/', $orderCode, $matches);
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

    public function createPaymentLinkForInvoice(Request $request, $invoiceId)
    {
        try {
            Log::info('PayOS Create Payment Link Request:', [
                'invoice_id' => $invoiceId,
                'return_url' => $request->returnUrl,
                'cancel_url' => $request->cancelUrl
            ]);

            $invoice = Invoice::with(['order.items.product', 'order.items.service', 'user'])
                ->findOrFail($invoiceId);

            // Generate orderCode using last 8 digits
            $orderCode = intval(substr(time() . rand(10, 99), -8));

            // Simplified description - maximum 25 characters
            $description = sprintf('HD%d', $invoice->id);

            $paymentData = [
                'orderCode' => $orderCode,
                'amount' => (int)($invoice->remaining_amount * 100), // Convert to smallest currency unit
                'description' => $description,
                'returnUrl' => $request->returnUrl,
                'cancelUrl' => $request->cancelUrl,
            ];

            // Add buyer info only if available and valid
            if ($invoice->user) {
                if ($invoice->user->full_name) {
                    $paymentData['buyerName'] = substr($invoice->user->full_name, 0, 255);
                }
                if ($invoice->user->email) {
                    $paymentData['buyerEmail'] = substr($invoice->user->email, 0, 255);
                }
                if ($invoice->user->phone_number) {
                    $paymentData['buyerPhone'] = substr($invoice->user->phone_number, 0, 20);
                }
                if ($invoice->user->address) {
                    $paymentData['buyerAddress'] = substr($invoice->user->address, 0, 255);
                }
            }

            Log::info('PayOS Payment Data:', $paymentData);

            $response = $this->payOS->createPaymentLink($paymentData);

            Log::info('PayOS Response:', $response);

            if (isset($response['checkoutUrl'])) {
                // Create payment history record with both old and new status
                PaymentHistory::create([
                    'invoice_id' => $invoice->id,
                    'amount' => $invoice->remaining_amount,
                    'payment_method' => 'payos',
                    'old_payment_status' => 'pending',
                    'new_payment_status' => 'pending',
                    'transaction_code' => $orderCode,
                ]);

                return response()->json([
                    'success' => true,
                    'data' => [
                        'checkoutUrl' => $response['checkoutUrl'],
                        'qrCode' => $response['qrCode'] ?? null,
                        'orderCode' => $orderCode,
                        'invoice_id' => $invoice->id,
                        'amount' => $invoice->remaining_amount,
                        'payment_method' => 'payos'
                    ]
                ]);
            }

            throw new \Exception($response['desc'] ?? 'Không thể tạo link thanh toán');
        } catch (\Exception $e) {
            Log::error('PayOS Create Payment Link Error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'invoice_id' => $invoiceId
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Không thể tạo link thanh toán: ' . $e->getMessage()
            ], 500);
        }
    }
}
