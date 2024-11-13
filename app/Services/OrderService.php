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

            DB::commit();
            return $order->load('order_items');
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
