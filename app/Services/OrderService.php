<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;

class OrderService
{
    public function createOrder(array $data)
    {
        $order = Order::create([
            'user_id' => $data['user_id'],
            'total_amount' => $data['total_amount'],
            'payment_method_id' => $data['payment_method_id'],
            'voucher_id' => $data['voucher_id'],
            'discount_amount' => $data['discount_amount'],
            'status' => 'pending',
        ]);

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

        return $order->load('items');
    }
}
