<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentHistory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class InvoiceService
{
    protected $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function getInvoices()
    {
        try {
            $invoices = Invoice::with(['user', 'order'])
                ->latest()
                ->paginate(10);

            return $invoices;
        } catch (\Exception $e) {
            Log::error('Error retrieving invoices:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    public function createInvoice(array $data)
    {
        DB::beginTransaction();
        try {
            // Create Order first
            $order = Order::create([
                'user_id' => $data['user_id'],
                'total_amount' => $data['total_amount'],
                'payment_method_id' => $data['payment_method_id'],
                'voucher_id' => $data['voucher_id'],
                'discount_amount' => $data['discount_amount'],
                'status' => 'pending',
            ]);

            // Create Order Items
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

            // Create Invoice
            $invoice = Invoice::create([
                'user_id' => $data['user_id'],
                'staff_user_id' => Auth::id(),
                'total_amount' => $data['total_amount'],
                'paid_amount' => 0,
                'status' => 'pending',
                'order_id' => $order->id,
                'note' => $data['note'] ?? null,
                'created_by_user_id' => Auth::id(),
            ]);

            DB::commit();
            return $invoice->load(['order.items', 'user']);

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function processPayment(Invoice $invoice, array $data)
    {
        try {
            DB::beginTransaction();

            $oldStatus = $invoice->status;
            $newPaidAmount = $invoice->paid_amount + $data['payment_amount'];
            
            $newStatus = 'pending';
            if ($newPaidAmount >= $invoice->total_amount) {
                $newStatus = 'paid';
            } elseif ($newPaidAmount > 0) {
                $newStatus = 'partial';
            }

            // Cập nhật invoice
            $invoice->update([
                'paid_amount' => $newPaidAmount,
                'status' => $newStatus,
            ]);

            // Tạo lịch sử thanh toán với đầy đủ thông tin
            PaymentHistory::create([
                'invoice_id' => $invoice->id,
                'old_payment_status' => $oldStatus,
                'new_payment_status' => $newStatus,
                'payment_amount' => $data['payment_amount'],
                'payment_method' => $data['payment_method'],
                'payment_proof' => $data['payment_proof'] ?? null,
                'created_by_user_id' => Auth::id(),
                'note' => $data['note'] ?? null,
            ]);

            DB::commit();
            return $invoice->fresh(['paymentHistories']);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
