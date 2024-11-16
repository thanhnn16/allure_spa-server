<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\UserServicePackage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderService
{
    protected $firebaseService;
    protected $notificationService;

    public function __construct(
        FirebaseService $firebaseService,
        NotificationService $notificationService
    ) {
        $this->firebaseService = $firebaseService;
        $this->notificationService = $notificationService;
    }

    public function createOrder(array $data)
    {
        try {
            DB::beginTransaction();

            // Validate and prepare data
            $orderData = [
                'user_id' => Auth::user()->id,
                'total_amount' => $data['total_amount'],
                'payment_method_id' => $data['payment_method_id'],
                'status' => request()->is('api/*') ? 'pending' : 'confirmed',
            ];

            // Optional fields
            if (isset($data['voucher_id'])) {
                $orderData['voucher_id'] = $data['voucher_id'];
            }

            if (isset($data['discount_amount'])) {
                $orderData['discount_amount'] = $data['discount_amount'];
            }

            if (isset($data['note'])) {
                $orderData['note'] = $data['note'];
            }

            $order = Order::create($orderData);

            // Create order items
            foreach ($data['order_items'] as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'item_type' => $item['item_type'],
                    'item_id' => $item['item_id'],
                    'service_type' => $item['service_type'] ?? null,
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                ]);
            }

            // Gửi thông báo cho admin
            $this->notificationService->notifyAdmins(
                'Đơn hàng mới',
                "Có đơn hàng mới từ khách hàng {$order->user->full_name}",
                'new_order',
                [
                    'type' => 'order',
                    'order_id' => $order->id,
                    'action' => 'created'
                ]
            );

            // Gửi thông báo cho khách hàng
            $this->notificationService->createNotification([
                'user_id' => $order->user_id,
                'title' => 'Đặt hàng thành công',
                'content' => "Đơn hàng #{$order->id} đã được tạo thành công",
                'type' => 'order_created'
            ]);

            DB::commit();
            return $order->load('order_items');
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function updateOrderStatus($order, $status, $note = null)
    {
        try {
            // Kiểm tra flow trạng thái hợp lệ
            if (!$this->isValidStatusTransition($order->status, $status)) {
                throw new \Exception('Không thể cập nhật sang trạng thái này');
            }

            // Kiểm tra ràng buộc với invoice
            if ($status === Order::STATUS_COMPLETED) {
                if (!$order->invoice) {
                    throw new \Exception('Không thể hoàn thành đơn hàng chưa có hóa đơn');
                }
                
                if ($order->invoice->status !== Invoice::STATUS_PAID) {
                    throw new \Exception('Không thể hoàn thành đơn hàng chưa thanh toán đủ');
                }
            }

            DB::beginTransaction();

            $updateData = [
                'status' => $status,
                'note' => $note
            ];

            // Thêm thông tin hủy đơn nếu status là cancelled
            if ($status === Order::STATUS_CANCELLED) {
                // Kiểm tra nếu đã thanh toán thì không được hủy
                if ($order->invoice && $order->invoice->status === Invoice::STATUS_PAID) {
                    throw new \Exception('Không thể hủy đơn hàng đã thanh toán');
                }

                $updateData = array_merge($updateData, [
                    'cancelled_by_user_id' => Auth::id(),
                    'cancelled_at' => now(),
                    'cancel_reason' => $note
                ]);
            }

            // Cập nhật đơn hàng
            $order->update($updateData);

            // Chuẩn bị thông báo dựa trên trạng thái
            $notificationData = $this->prepareStatusNotification($order, $status);

            // Gửi thông báo cho khách hàng
            if ($notificationData) {
                $this->notificationService->createNotification([
                    'user_id' => $order->user_id,
                    'title' => $notificationData['title'],
                    'content' => $notificationData['content'],
                    'type' => 'order_status_changed'
                ]);
            }

            // Gửi thông báo cho admin khi đơn hàng bị hủy
            if ($status === 'cancelled') {
                $this->notificationService->notifyAdmins(
                    'Đơn hàng bị hủy',
                    "Đơn hàng #{$order->id} đã bị hủy bởi " . 
                    (Auth::user()->role === 'admin' ? 'Admin' : 'khách hàng'),
                    'order_cancelled',
                    [
                        'type' => 'order',
                        'order_id' => $order->id,
                        'action' => 'cancelled'
                    ]
                );
            }

            DB::commit();
            return $order;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function prepareStatusNotification($order, $status)
    {
        $notifications = [
            'confirmed' => [
                'title' => 'Đơn hàng đã được xác nhận',
                'content' => "Đơn hàng #{$order->id} của bạn đã được xác nhận và đang được xử lý"
            ],
            'shipping' => [
                'title' => 'Đơn hàng đang được giao',
                'content' => "Đơn hàng #{$order->id} của bạn đang được giao đến bạn"
            ],
            'completed' => [
                'title' => 'Đơn hàng hoàn thành',
                'content' => "Đơn hàng #{$order->id} đã được giao thành công và hoàn thành. Cảm ơn bạn đã mua hàng!"
            ],
            'cancelled' => [
                'title' => 'Đơn hàng đã bị hủy',
                'content' => "Đơn hàng #{$order->id} đã bị hủy"
            ]
        ];

        return $notifications[$status] ?? null;
    }

    // Thêm method mới để kiểm tra flow trạng thái
    private function isValidStatusTransition($currentStatus, $newStatus)
    {
        // Định nghĩa flow cho phép
        $allowedTransitions = [
            Order::STATUS_PENDING => [Order::STATUS_CONFIRMED, Order::STATUS_CANCELLED],
            Order::STATUS_CONFIRMED => [Order::STATUS_SHIPPING, Order::STATUS_CANCELLED],
            Order::STATUS_SHIPPING => [Order::STATUS_COMPLETED],
            Order::STATUS_COMPLETED => [], // Trạng thái cuối
            Order::STATUS_CANCELLED => [], // Trạng thái cuối
        ];

        return in_array($newStatus, $allowedTransitions[$currentStatus] ?? []);
    }

    public function completeOrder(Order $order)
    {
        DB::transaction(function () use ($order) {
            // Update order status
            $order->status = Order::STATUS_COMPLETED;
            $order->save();

            // Process service packages for combo items
            foreach ($order->order_items as $item) {
                if ($item->item_type === 'service' && $item->service_type) {
                    $totalSessions = $this->getSessionsFromServiceType($item->service_type);
                    
                    UserServicePackage::create([
                        'user_id' => $order->user_id,
                        'service_id' => $item->item_id,
                        'total_sessions' => $totalSessions,
                        'used_sessions' => 0,
                        'expiry_date' => now()->addDays(365), // Set appropriate expiry
                        'is_combo' => true,
                        'combo_type' => $this->mapServiceTypeToComboType($item->service_type),
                        'order_id' => $order->id
                    ]);
                }
            }
        });
    }

    private function getSessionsFromServiceType(string $serviceType): int 
    {
        return match($serviceType) {
            'combo_5' => 5,
            'combo_10' => 10,
            default => 1
        };
    }

    private function mapServiceTypeToComboType(string $serviceType): ?string 
    {
        return match($serviceType) {
            'combo_5' => '5_times',
            'combo_10' => '10_times',
            default => null
        };
    }
}
