<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::with(['user', 'invoice'])
            ->when(request('status'), function($query, $status) {
                return $query->where('status', $status);
            })
            ->when(request('search'), function($query, $search) {
                return $query->whereHas('user', function($q) use ($search) {
                    $q->where('full_name', 'like', "%{$search}%");
                })->orWhere('id', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(10);

        return Inertia::render('Order/OrderIndex', [
            'orders' => $orders
        ]);
    }

    public function show(Order $order)
    {
        $order->load(['user', 'order_items']);
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
        $validatedData = $request->validate([
            'status' => 'required|in:pending,processing,completed,cancelled',
            'order_items' => 'required|array',
            'order_items.*.quantity' => 'required|integer|min:1',
            'order_items.*.price' => 'required|numeric|min:0',
            'note' => 'nullable|string',
        ]);

        $order->update([
            'status' => $validatedData['status'],
            'note' => $validatedData['note'],
        ]);

        foreach ($validatedData['order_items'] as $item) {
            $order->order_items()->where('id', $item['id'])->update([
                'quantity' => $item['quantity'],
                'price' => $item['price'],
            ]);
        }

        $order->total_amount = $order->order_items->sum(function ($item) {
            return $item->quantity * $item->price;
        });
        $order->save();

        return redirect()->route('orders.show', $order->id)->with('success', 'Đơn hàng đã được cập nhật thành công.');
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
                'data' => $order->load(['items', 'user']),
                'message' => 'Đơn hàng đã được tạo thành công'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
