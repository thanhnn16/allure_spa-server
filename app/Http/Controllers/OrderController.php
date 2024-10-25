<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::with('user')->latest()->paginate(10);
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
}
