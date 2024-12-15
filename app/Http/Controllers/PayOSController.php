<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\PaymentHistory;
use PayOS\PayOS;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Services\NotificationService;
use App\Models\FcmToken;
use App\Models\Order;
use App\Services\FirebaseService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class PayOSController extends Controller
{
    protected $payOS;

    public function __construct()
    {
        $this->payOS = app('payos');
    }

    public function createPaymentLink(Request $request, Order $order)
    {
        try {
            // Validate order status
            if ($order->status !== 'pending') {
                throw new \Exception('Đơn hàng không ở trạng thái cho phép thanh toán');
            }

            // Tạo mã đơn hàng unique
            $orderCode = 'PAY' . time() . rand(1000, 9999);

            // Chuẩn bị dữ liệu thanh toán
            $paymentData = [
                'orderCode' => $orderCode,
                'amount' => (int)$order->total_amount,
                'description' => "Thanh toán đơn hàng #{$order->id}",
                'returnUrl' => $request->returnUrl,
                'cancelUrl' => $request->cancelUrl,
                'buyerName' => $order->user->full_name ?? '',
                'buyerEmail' => $order->user->email ?? '',
                'buyerPhone' => $order->user->phone_number ?? '',
                'orderInfo' => json_encode([
                    'order_id' => $order->id,
                    'invoice_id' => $order->invoice->id ?? null
                ])
            ];

            // Tạo payment link từ PayOS
            $response = $this->payOS->createPaymentLink($paymentData);

            if (!isset($response['checkoutUrl'])) {
                throw new \Exception('Không thể tạo link thanh toán');
            }

            // Lưu lịch sử thanh toán
            PaymentHistory::create([
                'order_id' => $order->id,
                'invoice_id' => $order->invoice->id,
                'payment_amount' => $order->total_amount,
                'payment_method' => 'payos',
                'transaction_code' => $orderCode,
                'old_payment_status' => 'pending',
                'new_payment_status' => 'pending'
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'checkoutUrl' => $response['checkoutUrl'],
                    'qrCode' => $response['qrCode'] ?? null,
                    'orderCode' => $orderCode,
                    'amount' => $order->total_amount
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('PayOS payment error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Lỗi xử lý thanh toán: ' . $e->getMessage()
            ], 500);
        }
    }

    public function processPayment(Request $request, $orderId)
    {
        try {
            Log::info('Bắt đầu xử lý thanh toán PayOS:', [
                'order_id' => $orderId,
                'request_data' => $request->all()
            ]);

            $order = Order::with(['user', 'invoice'])->findOrFail($orderId);
            
            // Tạo mã đơn hàng unique
            $orderCode = 'PAY' . time() . rand(1000, 9999);
            
            // Chuẩn bị dữ liệu thanh toán theo đúng format của PayOS
            $paymentData = [
                'orderCode' => $orderCode,
                'amount' => (int)$order->total_amount,
                'description' => "Thanh toán đơn hàng #{$order->id}",
                'returnUrl' => $request->returnUrl,
                'cancelUrl' => $request->cancelUrl,
                'buyerName' => $order->user->full_name ?? '',
                'buyerEmail' => $order->user->email ?? '',
                'buyerPhone' => $order->user->phone_number ?? '',
                'buyerAddress' => $order->user->address ?? '',
                'items' => [
                    [
                        'name' => "Đơn hàng #{$order->id}",
                        'quantity' => 1,
                        'price' => (int)$order->total_amount
                    ]
                ]
            ];

            // Nếu có danh sách sản phẩm chi tiết
            if ($order->orderItems->count() > 0) {
                $paymentData['items'] = $this->formatOrderItems($order->orderItems);
            }

            try {
                // Tạo payment link từ PayOS
                $response = $this->payOS->createPaymentLink($paymentData);

                if (!isset($response['checkoutUrl'])) {
                    throw new \Exception('Không thể tạo link thanh toán: ' . json_encode($response));
                }

                // Lưu lịch sử thanh toán
                PaymentHistory::create([
                    'order_id' => $order->id,
                    'invoice_id' => $order->invoice->id,
                    'payment_amount' => $order->total_amount,
                    'payment_method' => 'payos',
                    'transaction_code' => $orderCode,
                    'old_payment_status' => 'pending',
                    'new_payment_status' => 'pending',
                    'payment_details' => json_encode([
                        'request' => $paymentData,
                        'response' => $response
                    ])
                ]);

                return response()->json([
                    'success' => true,
                    'data' => [
                        'checkoutUrl' => $response['checkoutUrl'],
                        'qrCode' => $response['qrCode'] ?? null,
                        'orderCode' => $orderCode,
                        'amount' => $order->total_amount
                    ]
                ]);

            } catch (\Exception $e) {
                Log::error('Lỗi khi tạo payment link PayOS:', [
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                throw $e;
            }

        } catch (\Exception $e) {
            Log::error('Lỗi xử lý thanh toán PayOS:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
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
                $amount = isset($response['amount']) ? $response['amount'] : 0;

                // Tìm payment history dựa vào orderCode
                $paymentHistory = PaymentHistory::where('transaction_code', $orderCode)
                    ->with(['invoice.user.fcmTokens'])
                    ->first();

                if ($paymentStatus === 'PAID' && $paymentHistory) {
                    DB::beginTransaction();
                    try {
                        $invoice = $paymentHistory->invoice;
                        $oldStatus = $invoice->status;
                        $newPaidAmount = $invoice->paid_amount + $amount;

                        // Cập nhật trạng thái invoice
                        $newStatus = $newPaidAmount >= $invoice->total_amount ?
                            Invoice::STATUS_PAID :
                            Invoice::STATUS_PARTIALLY_PAID;

                        $invoice->update([
                            'status' => $newStatus,
                            'paid_amount' => $newPaidAmount
                        ]);

                        // Cập nhật trạng thái đơn hàng nếu có
                        if ($invoice->order && $newStatus === Invoice::STATUS_PAID) {
                            $invoice->order->update([
                                'status' => Order::STATUS_COMPLETED
                            ]);
                        }

                        // Cập nhật payment history
                        $paymentHistory->update([
                            'old_payment_status' => $oldStatus,
                            'new_payment_status' => $newStatus,
                            'payment_amount' => $amount,
                            'payment_details' => json_encode($response)
                        ]);

                        // Gửi thông báo
                        try {
                            $notificationService = app(NotificationService::class);
                            $formattedAmount = number_format($amount, 0, ',', '.') . ' VNĐ';

                            // Thông báo cho người dùng
                            if ($invoice->user) {
                                $notificationService->createNotification([
                                    'user_id' => $invoice->user_id,
                                    'type' => 'payment_success',
                                    'data' => [
                                        'invoice_id' => $invoice->id,
                                        'amount' => $formattedAmount,
                                        'order_id' => $invoice->order_id
                                    ],
                                    'send_fcm' => true
                                ]);
                            }
                            // Thông báo cho admin
                            $notificationService->notifyAdmins(
                                'Thanh toán thành công',
                                "Khách hàng {$invoice->user->full_name} đã thanh toán {$formattedAmount}",
                                'payment_success',
                                [
                                    'invoice_id' => $invoice->id,
                                    'amount' => $formattedAmount,
                                    'user_id' => $invoice->user_id
                                ]
                            );
                        } catch (\Exception $e) {
                            Log::error('Failed to send payment notifications:', [
                                'message' => $e->getMessage(),
                                'trace' => $e->getTraceAsString()
                            ]);
                        }

                        DB::commit();

                        return response()->json([
                            'success' => true,
                            'data' => [
                                'status' => $paymentStatus,
                                'amount' => $amount,
                                'orderCode' => $orderCode,
                                'paymentTime' => $response['createdAt'] ?? null,
                            ]
                        ]);
                    } catch (\Exception $e) {
                        DB::rollBack();
                        throw $e;
                    }
                }

                return response()->json([
                    'success' => false,
                    'message' => 'Trạng thái thanh toán không hợp lệ'
                ], 400);
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
}
