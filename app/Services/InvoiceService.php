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

    public function getInvoices($filters = [])
    {
        try {
            $query = Invoice::with(['user', 'order']);

            // Apply status filter
            if (!empty($filters['status'])) {
                $query->where('status', $filters['status']);
            }

            // Apply search filter
            if (!empty($filters['search'])) {
                $search = $filters['search'];
                $query->where(function ($q) use ($search) {
                    $q->where('id', 'LIKE', "%{$search}%")
                        ->orWhereHas('user', function ($q) use ($search) {
                            $q->where('full_name', 'LIKE', "%{$search}%");
                        });
                });
            }

            return $query->latest()->paginate(10);
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
            return $invoice->load(['order.orderItems', 'user']);
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

            $newStatus = Invoice::STATUS_PENDING;
            if ($newPaidAmount >= $invoice->total_amount) {
                $newStatus = Invoice::STATUS_PAID;

                // Kiểm tra và cập nhật trạng thái đơn hàng
                if ($invoice->order) {
                    // Nếu đơn hàng đang giao -> có thể chuyển sang hoàn thành
                    if ($invoice->order->status === Order::STATUS_SHIPPING) {
                        $invoice->order->update([
                            'status' => Order::STATUS_COMPLETED
                        ]);
                    }
                }
            } elseif ($newPaidAmount > 0) {
                $newStatus = Invoice::STATUS_PARTIALLY_PAID;
            }

            // Update invoice
            $invoice->update([
                'paid_amount' => $newPaidAmount,
                'status' => $newStatus,
            ]);

            // Create payment history
            PaymentHistory::create([
                'invoice_id' => $invoice->id,
                'old_payment_status' => $oldStatus,
                'new_payment_status' => $newStatus,
                'payment_amount' => $data['payment_amount'],
                'payment_method' => $data['payment_method_id'],
                'payment_proof' => $data['payment_proof'] ?? null,
                'created_by_user_id' => Auth::id(),
                'note' => $data['note'] ?? null,
            ]);

            DB::commit();
            return $invoice->fresh(['paymentHistories', 'order']);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
