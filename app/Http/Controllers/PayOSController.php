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

                // Extract invoice ID from orderCode (format: INV{id}_timestamp)
                preg_match('/INV(\d+)_/', $orderCode, $matches);
                $invoiceId = $matches[1] ?? null;

                if ($paymentStatus === 'PAID' && $invoiceId) {
                    DB::beginTransaction();
                    try {
                        // Find invoice and related payment history
                        $invoice = Invoice::with(['user', 'user.fcmTokens'])->findOrFail($invoiceId);

                        // Update invoice status and paid amount
                        $oldStatus = $invoice->status;
                        $newPaidAmount = $invoice->paid_amount + $amount;

                        $invoice->update([
                            'status' => 'paid',
                            'paid_amount' => $newPaidAmount
                        ]);

                        // Update order status if exists
                        if ($invoice->order) {
                            $invoice->order->update([
                                'status' => 'completed'
                            ]);
                        }

                        // Create payment history record
                        PaymentHistory::create([
                            'invoice_id' => $invoice->id,
                            'old_payment_status' => $oldStatus,
                            'new_payment_status' => 'paid',
                            'payment_amount' => $amount,
                            'payment_method' => 'payos',
                            'payment_details' => json_encode($response),
                            'note' => 'Thanh toán thành công qua PayOS'
                        ]);

                        // Send notifications
                        try {
                            $firebaseService = app(FirebaseService::class);
                            $formattedAmount = number_format($amount, 0, ',', '.') . ' VNĐ';

                            // Notification data
                            $notificationData = [
                                'type' => 'payment_success',
                                'invoice_id' => $invoice->id,
                                'amount' => $formattedAmount
                            ];

                            // Send to admin
                            $firebaseService->sendNotificationToAdmin(
                                'Thanh toán thành công',
                                "Khách hàng {$invoice->user->full_name} đã thanh toán {$formattedAmount} qua PayOS",
                                $notificationData
                            );

                            // Send to user
                            if ($invoice->user && $invoice->user->fcmTokens) {
                                foreach ($invoice->user->fcmTokens as $fcmToken) {
                                    $firebaseService->sendMessage(
                                        $fcmToken->token,
                                        'Thanh toán thành công',
                                        "Bạn đã thanh toán thành công số tiền {$formattedAmount}",
                                        array_merge($notificationData, ['user_id' => $invoice->user_id])
                                    );
                                }
                            }
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
            $description = sprintf('Thanh toán cho Allure Spa');

            $paymentData = [
                'orderCode' => $orderCode,
                'amount' => (int)($invoice->remaining_amount),
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
                    'payment_amount' => $invoice->remaining_amount,
                    'payment_method' => 'payos',
                    'old_payment_status' => 'pending',
                    'new_payment_status' => 'pending',
                    'transaction_code' => $orderCode,
                    'note' => 'Khởi tạo thanh toán qua PayOS'
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
