<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
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
            DB::beginTransaction();

            $oldStatus = $order->status;
            $order->update([
                'status' => $status,
                'note' => $note
            ]);

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
                    "Đơn hàng #{$order->id} đã bị hủy bởi khách hàng",
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
}
