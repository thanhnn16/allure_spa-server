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

            // Tính toán số tiền cần thanh toán (sau khi áp dụng giảm giá)
            $finalAmount = $order->total_amount - ($order->discount_amount ?? 0);

            // Chuẩn bị dữ liệu thanh toán
            $paymentData = [
                'orderCode' => $orderCode,
                'amount' => (int)$finalAmount, // Sử dụng số tiền sau giảm giá
                'description' => "Thanh toán đơn hàng #{$order->id}",
                'returnUrl' => $request->returnUrl,
                'cancelUrl' => $request->cancelUrl,
                'buyerName' => $order->user->full_name ?? '',
                'buyerEmail' => $order->user->email ?? '',
                'buyerPhone' => $order->user->phone_number ?? '',
                'expiredAt' => time() + (10 * 60),
                'orderInfo' => json_encode([
                    'order_id' => $order->id,
                    'invoice_id' => $order->invoice->id ?? null,
                    'original_amount' => $order->total_amount,
                    'discount_amount' => $order->discount_amount ?? 0
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
                'payment_amount' => $finalAmount, // Lưu số tiền sau giảm giá
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
                    'amount' => $finalAmount // Trả về số tiền sau giảm giá
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

            // Tạo mã đơn hàng là số nguyên dương
            $orderCode = (int) substr(time() . rand(1000, 9999), 0, 15);

            // Tính toán số tiền sau giảm giá
            $finalAmount = $order->total_amount - ($order->discount_amount ?? 0);

            // Xử lý số tiền thanh toán (đảm bảo không vượt quá 10 tỷ VND)
            $amount = min((int)$finalAmount, 10000000000);

            // Tạo invoice nếu chưa có
            if (!$order->invoice) {
                DB::beginTransaction();
                try {
                    $invoice = new Invoice([
                        'user_id' => $order->user_id,
                        'order_id' => $order->id,
                        'total_amount' => $order->total_amount,
                        'paid_amount' => 0,
                        'status' => Invoice::STATUS_PENDING,
                        'created_by_user_id' => Auth::user()->id
                    ]);
                    $invoice->save();
                    $order->invoice()->save($invoice);
                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollBack();
                    throw new \Exception('Không thể tạo hóa đơn: ' . $e->getMessage());
                }
            }

            // Refresh order để lấy invoice mới tạo
            $order->refresh();

            // Chuẩn bị dữ liệu thanh toán theo đúng format của PayOS
            $paymentData = [
                'orderCode' => $orderCode,
                'amount' => $amount,
                'description' => "Thanh toán đơn hàng #{$order->id}",
                'returnUrl' => $request->returnUrl,
                'cancelUrl' => $request->cancelUrl,
                'buyerName' => $order->user->full_name ?? '',
                'buyerEmail' => $order->user->email ?? '',
                'buyerPhone' => $order->user->phone_number ?? '',
                'buyerAddress' => $order->user->address ?? '',
                'expiredAt' => time() + (10 * 60),
                'items' => [
                    [
                        'name' => "Đơn hàng #{$order->id}",
                        'quantity' => 1,
                        'price' => min((int)$order->total_amount, 10000000000)
                    ]
                ]
            ];

            // Nếu có danh sách sản phẩm chi tiết
            if ($order->orderItems->count() > 0) {
                $paymentData['items'] = $order->orderItems->map(function ($item) {
                    return [
                        'name' => $item->item_type === 'product' ?
                            ($item->product->name ?? 'Sản phẩm') : ($item->service->name ?? 'Dịch vụ'),
                        'quantity' => (int)$item->quantity,
                        'price' => min((int)$item->price, 10000000000)
                    ];
                })->toArray();
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
                    'payment_amount' => $amount,
                    'payment_method' => 'payos',
                    'transaction_code' => (string)$orderCode,
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
                        'orderCode' => (string)$orderCode,
                        'amount' => $amount
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

    public function verifyPayment(Request $request)
    {
        try {
            Log::info('Verify payment request:', $request->all());
            
            // Validate request
            $validated = $request->validate([
                'orderCode' => 'required|string',
                'status' => 'required|string'
            ]);

            $orderCode = $validated['orderCode'];
            $status = $validated['status'];

            // Kiểm tra trạng thái từ callback trước
            if ($status !== 'PAID') {
                return response()->json([
                    'success' => false,
                    'message' => 'Trạng thái thanh toán không hợp lệ'
                ], 400);
            }

            // Verify với PayOS
            $response = $this->payOS->getPaymentLinkInformation($orderCode);
            Log::info('PayOS Verification Response:', $response);

            if ($response && isset($response['status']) && $response['status'] === 'PAID') {
                // Tìm payment history
                $paymentHistory = PaymentHistory::where('transaction_code', $orderCode)
                    ->with(['invoice.user.fcmTokens', 'invoice.order'])
                    ->first();

                if (!$paymentHistory) {
                    throw new \Exception('Không tìm thấy thông tin thanh toán');
                }

                DB::beginTransaction();
                try {
                    $invoice = $paymentHistory->invoice;
                    $order = $invoice->order;
                    $oldStatus = $invoice->status;
                    $amount = $response['amount'] ?? 0;
                    $newPaidAmount = $invoice->paid_amount + $amount;

                    // Cập nhật invoice
                    $newStatus = $newPaidAmount >= $invoice->total_amount ? 
                        Invoice::STATUS_PAID : 
                        Invoice::STATUS_PARTIALLY_PAID;

                    $invoice->update([
                        'status' => $newStatus,
                        'paid_amount' => $newPaidAmount
                    ]);

                    // Nếu đã thanh toán đủ, cập nhật trạng thái đơn hàng
                    if ($newStatus === Invoice::STATUS_PAID && $order) {
                        // Nếu đơn hàng đang ở trạng thái pending hoặc confirmed
                        if (in_array($order->status, [Order::STATUS_PENDING, Order::STATUS_CONFIRMED])) {
                            $order->update([
                                'status' => Order::STATUS_CONFIRMED
                            ]);
                        }
                        // Nếu đơn hàng đang shipping
                        else if ($order->status === Order::STATUS_SHIPPING) {
                            $order->update([
                                'status' => Order::STATUS_COMPLETED
                            ]);
                        }
                    }

                    // Cập nhật payment history
                    $paymentHistory->update([
                        'old_payment_status' => $oldStatus,
                        'new_payment_status' => $newStatus,
                        'payment_amount' => $amount,
                        'payment_details' => json_encode($response)
                    ]);

                    // Gửi thông báo
                    if ($invoice->user) {
                        $notificationService = app(NotificationService::class);
                        $formattedAmount = number_format($amount, 0, ',', '.') . ' VNĐ';
                        
                        // Thông báo thanh toán thành công
                        $notificationService->createNotification([
                            'user_id' => $invoice->user_id,
                            'type' => 'payment_success',
                            'data' => [
                                'invoice_id' => $invoice->id,
                                'amount' => $formattedAmount,
                                'order_id' => $order->id
                            ],
                            'send_fcm' => true
                        ]);

                        // Thông báo đơn hàng đã hoàn thành (nếu có)
                        if ($order && $order->status === Order::STATUS_COMPLETED) {
                            $notificationService->createNotification([
                                'user_id' => $invoice->user_id,
                                'type' => 'order_completed',
                                'data' => [
                                    'order_id' => $order->id
                                ],
                                'send_fcm' => true
                            ]);
                        }
                    }

                    DB::commit();

                    return response()->json([
                        'success' => true,
                        'data' => [
                            'status' => 'PAID',
                            'amount' => $amount,
                            'orderCode' => $orderCode,
                            'transactionId' => $response['transactionId'] ?? null,
                            'orderId' => $order->id,
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
                'message' => 'Không thể xác thực thanh toán'
            ], 400);

        } catch (\Exception $e) {
            Log::error('Verify payment error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function mobileCallback(Request $request)
    {
        try {
            $orderId = $request->query('order_id');
            $status = $request->query('status');

            // Tạo deep link URL để mở app
            $scheme = "allurespa://invoice/";

            if ($status === 'success') {
                // Lấy thông tin thanh toán
                $order = Order::with('invoice')->find($orderId);

                return view('payment.redirect', [
                    'redirectUrl' => $scheme . "success?" . http_build_query([
                        'order_id' => $orderId,
                        'amount' => $order->total_amount,
                        'payment_time' => now()->toISOString(),
                        'payment_method' => 'bank_transfer'
                    ])
                ]);
            } else {
                return view('payment.redirect', [
                    'redirectUrl' => $scheme . "failed?" . http_build_query([
                        'order_id' => $orderId,
                        'type' => 'failed',
                        'reason' => 'Payment failed'
                    ])
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Payment callback error:', [
                'message' => $e->getMessage(),
                'data' => $request->all()
            ]);

            return view('payment.redirect', [
                'redirectUrl' => $scheme . "failed?reason=system_error"
            ]);
        }
    }
}
