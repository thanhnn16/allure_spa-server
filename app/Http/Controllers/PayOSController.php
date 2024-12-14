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

class PayOSController extends Controller
{
    protected $payOS;

    public function __construct(PayOS $payOS)
    {
        $this->payOS = $payOS;
    }

    public function createPaymentLink(array $paymentData)
    {
        try {
            // Validate payment data
            if (!is_array($paymentData)) {
                throw new \Exception('Invalid payment data format');
            }

            // Ensure required fields exist
            $requiredFields = ['orderCode', 'amount', 'description'];
            foreach ($requiredFields as $field) {
                if (!isset($paymentData[$field])) {
                    throw new \Exception("Missing required field: {$field}");
                }
            }

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
                'trace' => $e->getTraceAsString(),
                'paymentData' => $paymentData
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
            'description' => "Thanh toán cho Allure Spa",
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

    public function processPayment(Request $request, $orderId)
    {
        try {
            // Validate request
            $request->validate([
                'returnUrl' => 'required|string',
                'cancelUrl' => 'required|string'
            ]);

            $order = Order::with(['user', 'shippingAddress', 'invoice'])
                ->findOrFail($orderId);

            // Kiểm tra hoặc tạo invoice nếu chưa có
            if (!$order->invoice) {
                $invoice = new Invoice([
                    'user_id' => $order->user_id,
                    'order_id' => $order->id,
                    'total_amount' => $order->total_amount,
                    'paid_amount' => 0,
                    'status' => 'pending',
                    'payment_method' => 'payos'
                ]);
                $order->invoice()->save($invoice);
                $order->refresh();
            }

            // Generate order code
            $orderCode = intval(substr(time() . rand(10, 99), -8));

            // Prepare payment data as array
            $paymentData = [
                'orderCode' => $orderCode,
                'amount' => (int)$order->total_amount,
                'description' => "Thanh toán đơn hàng #{$order->id}",
                'returnUrl' => $request->returnUrl,
                'cancelUrl' => $request->cancelUrl,
            ];

            // Add buyer info if available
            if ($order->user) {
                if ($order->user->full_name) {
                    $paymentData['buyerName'] = substr($order->user->full_name, 0, 255);
                }
                if ($order->user->email) {
                    $paymentData['buyerEmail'] = substr($order->user->email, 0, 255);
                }
                if ($order->user->phone) {
                    $paymentData['buyerPhone'] = substr($order->user->phone, 0, 20);
                }
            }

            if ($order->shippingAddress) {
                $paymentData['buyerAddress'] = substr($order->shippingAddress->full_address ?? '', 0, 255);
            }

            // Gọi PayOS API để tạo payment link
            $result = $this->payOS->createPaymentLink($paymentData);

            if (!isset($result['checkoutUrl'])) {
                throw new \Exception($result['desc'] ?? 'Không thể tạo link thanh toán');
            }

            // Create payment history với invoice_id
            PaymentHistory::create([
                'order_id' => $order->id,
                'invoice_id' => $order->invoice->id,
                'payment_amount' => $order->total_amount,
                'payment_method' => 'payos',
                'old_payment_status' => 'pending',
                'new_payment_status' => 'pending',
                'transaction_code' => $orderCode,
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'checkoutUrl' => $result['checkoutUrl'],
                    'qrCode' => $result['qrCode'] ?? null,
                    'orderCode' => $orderCode,
                    'amount' => $order->total_amount,
                    'payment_method' => 'payos'
                ]
            ]);
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

    public function createPaymentLinkForInvoice(Request $request, $invoiceId)
    {
        try {
            // 1. Kiểm tra cấu hình PayOS
            if (!config('payos.client_id') || !config('payos.api_key') || !config('payos.checksum_key')) {
                throw new \Exception('Thiếu cấu hình PayOS');
            }

            $invoice = Invoice::with(['order.orderItems.product', 'order.orderItems.service', 'user'])
                ->findOrFail($invoiceId);

            // Kiểm tra số tiền còn lại phải thanh toán
            $remainingAmount = $invoice->remaining_amount;
            if ($remainingAmount <= 0) {
                throw new \Exception('Hóa đơn này đã được thanh toán đầy đủ');
            }

            // 2. Validate dữ liệu đầu vào
            $paymentData = [
                'orderCode' => intval(substr(time() . rand(10, 99), -8)),
                'amount' => (int)$remainingAmount,
                'description' => "Thanh toán cho Allure Spa",
                'returnUrl' => $request->returnUrl ?? config('app.url') . '/payment/callback',
                'cancelUrl' => $request->cancelUrl ?? config('app.url') . '/payment/callback?status=cancel',
            ];

            // 3. Log request data
            Log::info('PayOS Request Data:', $paymentData);

            // 4. Gọi API với try-catch riêng
            try {
                $response = $this->payOS->createPaymentLink($paymentData);
                
                if (!$response) {
                    throw new \Exception('Không nhận được phản hồi từ PayOS');
                }

                Log::info('PayOS Response:', $response);

                if (!isset($response['checkoutUrl'])) {
                    throw new \Exception('Response không hợp lệ từ PayOS');
                }

                // 5. Tạo payment history
                PaymentHistory::create([
                    'invoice_id' => $invoice->id,
                    'payment_amount' => $remainingAmount,
                    'payment_method' => 'payos',
                    'old_payment_status' => 'pending',
                    'new_payment_status' => 'pending',
                    'transaction_code' => $paymentData['orderCode'],
                    'note' => 'Khởi tạo thanh toán qua PayOS'
                ]);

                return response()->json([
                    'success' => true,
                    'data' => [
                        'checkoutUrl' => $response['checkoutUrl'],
                        'qrCode' => $response['qrCode'] ?? null,
                        'orderCode' => $paymentData['orderCode']
                    ]
                ]);

            } catch (\Exception $e) {
                Log::error('PayOS API Error:', [
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                throw new \Exception('Lỗi khi gọi API PayOS: ' . $e->getMessage());
            }

        } catch (\Exception $e) {
            Log::error('PayOS Create Payment Link Error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Không thể tạo link thanh toán: ' . $e->getMessage()
            ], 500);
        }
    }
}
