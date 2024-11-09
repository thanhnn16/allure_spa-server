<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Invoice;
use App\Models\PaymentMethod;
use App\Models\Voucher;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::with(['user', 'invoice'])
            ->when(request('status'), function ($query, $status) {
                return $query->where('status', $status);
            })
            ->when(request('search'), function ($query, $search) {
                return $query->whereHas('user', function ($q) use ($search) {
                    $q->where('full_name', 'like', "%{$search}%");
                })->orWhere('id', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(10);

        return Inertia::render('Order/OrderView', [
            'orders' => $orders
        ]);
    }

    public function show(Order $order)
    {
        $order->load([
            'user',
            'order_items.product',
            'order_items.service',
            'invoice'
        ]);
        return Inertia::render('Order/OrderShow', [
            'order' => $order
        ]);
    }

    public function edit(Order $order)
    {
        $order->load(['user', 'order_items']);
        return Inertia::render('Order/OrderEdit', [
            'order' => $order
        ]);
    }

    public function update(Request $request, Order $order)
    {
        try {
            $validated = $request->validate([
                'status' => 'required|in:pending,processing,completed,cancelled',
                'note' => 'nullable|string'
            ]);

            $order->update([
                'status' => $validated['status'],
                'note' => $validated['note']
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật trạng thái đơn hàng thành công',
                'data' => $order
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi cập nhật trạng thái đơn hàng',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Order $order)
    {
        $order->delete();
        return redirect()->route('orders.index')->with('success', 'Đơn hàng đã được xóa thành công.');
    }

    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'user_id' => 'required|exists:users,id',
                'voucher_id' => 'nullable|exists:vouchers,id',
                'payment_method_id' => 'required|exists:payment_methods,id',
                'order_items' => 'required|array|min:1',
                'order_items.*.item_type' => 'required|in:product,service',
                'order_items.*.item_id' => 'required|integer',
                'order_items.*.service_type' => 'nullable|in:single,combo_5,combo_10',
                'order_items.*.quantity' => 'required|integer|min:1',
                'order_items.*.price' => 'required|numeric|min:0',
                'note' => 'nullable|string',
                'total_amount' => 'required|numeric|min:0',
                'discount_amount' => 'required|numeric|min:0',
            ]);

            $order = Order::create([
                'user_id' => $validatedData['user_id'],
                'total_amount' => $validatedData['total_amount'],
                'payment_method_id' => $validatedData['payment_method_id'],
                'voucher_id' => $validatedData['voucher_id'],
                'discount_amount' => $validatedData['discount_amount'],
                'status' => 'pending',
            ]);

            // Create order items
            foreach ($validatedData['order_items'] as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'item_type' => $item['item_type'],
                    'item_id' => $item['item_id'],
                    'service_type' => $item['service_type'] ?? null,
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                ]);
            }

            return response()->json([
                'success' => true,
                'data' => $order->load(['order_items', 'user']),
                'message' => 'Đơn hàng đã được tạo thành công'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function createInvoice(Order $order)
    {
        try {
            // Kiểm tra xem đơn hàng đã có hóa đơn chưa
            if ($order->invoice) {
                throw new \Exception('Đơn hàng này đã có hóa đơn');
            }

            // Tạo hóa đơn mới
            $invoice = Invoice::create([
                'user_id' => $order->user_id,
                'staff_user_id' => Auth::user()->id,
                'total_amount' => $order->total_amount - $order->discount_amount,
                'paid_amount' => 0, // remaining_amount sẽ tự động được tính
                'status' => Invoice::STATUS_PENDING,
                'order_id' => $order->id,
                'note' => request('note'),
                'created_by_user_id' => Auth::user()->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Hóa đơn đã được tạo thành công',
                'data' => $invoice->load(['user', 'staff', 'order'])
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function create()
    {
        return Inertia::render('Order/OrderCreate', [
            'paymentMethods' => PaymentMethod::all(),
            'vouchers' => Voucher::where('status', 'active')->get(),
        ]);
    }
}
