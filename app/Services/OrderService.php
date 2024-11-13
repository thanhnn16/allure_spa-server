<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function createOrder(array $data)
    {
        try {
            DB::beginTransaction();

            // Determine initial status based on source
            $initialStatus = request()->is('api/*') ? 'pending' : 'confirmed';

            $order = Order::create([
                'user_id' => $data['user_id'] ?? Auth::user()->id,
                'total_amount' => $data['total_amount'],
                'payment_method_id' => $data['payment_method_id'],
                'voucher_id' => $data['voucher_id'] ?? null,
                'discount_amount' => $data['discount_amount'] ?? 0,
                'note' => $data['note'] ?? null,
                'status' => $initialStatus,
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

            DB::commit();
            return $order->load('order_items');
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
