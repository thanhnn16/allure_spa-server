<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
class InvoiceService
{
    public function getInvoices()
    {
        return Invoice::with(['user', 'order'])->latest()->paginate(10);
    }

    public function createInvoice(array $data)
    {
        DB::beginTransaction();

        try {
            $order = $this->createOrder($data);
            $this->createOrderItems($order, $data['order_items']);
            $invoice = $this->createInvoiceRecord($order, $data);

            DB::commit();

            return $invoice;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function createOrder(array $data): Order
    {
        return Order::create([
            'user_id' => $data['user_id'],
            'total_amount' => $data['total_amount'],
            'payment_method_id' => $data['payment_method_id'],
            'voucher_id' => $data['voucher_id'],
            'discount_amount' => $data['discount_amount'],
            'status' => 'pending',
        ]);
    }

    private function createOrderItems(Order $order, array $items): void
    {
        foreach ($items as $item) {
            OrderItem::create([
                'order_id' => $order->id,
                'item_type' => $item['item_type'],
                'item_id' => $item['item_id'],
                'service_type' => $item['service_type'] ?? null,
                'quantity' => $item['quantity'],
                'price' => $item['price'],
            ]);
        }
    }

    private function createInvoiceRecord(Order $order, array $data): Invoice
    {
        return Invoice::create([
            'user_id' => $data['user_id'],
            'staff_user_id' => Auth::user()->id,
            'total_amount' => $data['total_amount'],
            'paid_amount' => 0,
            'status' => 'pending',
            'order_id' => $order->id,
            'note' => $data['note'],
            'created_by_user_id' => Auth::user()->id,
        ]);
    }
}
