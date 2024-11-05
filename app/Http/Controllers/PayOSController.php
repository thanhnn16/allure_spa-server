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
        $orderCode = intval(time() . rand(1000, 9999));
        $amount = intval($request->amount ?? 2000);

        $paymentData = [
            'orderCode' => $orderCode,
            'amount' => $amount,
            'description' => $request->description ?? 'Test payment',
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

            if ($response && $response['status'] === 'PAID') {
                if (str_starts_with($orderCode, 'INV_')) {
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
            $invoice = Invoice::find($invoiceId);
            
            if (!$invoice) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy hóa đơn'
                ], 404);
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
            Log::error('PayOS Create Payment Link Error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'invoice_id' => $invoiceId
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Lỗi tạo link thanh toán: ' . $e->getMessage()
            ], 500);
        }
    }
}
