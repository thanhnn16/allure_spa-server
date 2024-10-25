<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\InvoiceService;
use App\Models\PaymentMethod;
use App\Models\Voucher;
use Illuminate\Support\Facades\Auth;

class InvoiceController extends BaseController
{
    protected $invoiceService;

    public function __construct(InvoiceService $invoiceService)
    {
        $this->invoiceService = $invoiceService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $invoices = $this->invoiceService->getInvoices();
        return $this->respondWithInertia('Invoice/InvoiceView', ['invoices' => $invoices]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $paymentMethods = PaymentMethod::all();
        $vouchers = Voucher::where('status', 'active')->get();
        return $this->respondWithInertia('Invoice/InvoiceCreate', [
            'paymentMethods' => $paymentMethods,
            'vouchers' => $vouchers,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
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

        DB::beginTransaction();

        try {
            // Create Order
            $order = Order::create([
                'user_id' => $validatedData['user_id'],
                'total_amount' => $validatedData['total_amount'],
                'payment_method_id' => $validatedData['payment_method_id'],
                'voucher_id' => $validatedData['voucher_id'],
                'discount_amount' => $validatedData['discount_amount'],
                'status' => 'pending',
            ]);

            // Create Order Items
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

            // Create Invoice
            $invoice = Invoice::create([
                'user_id' => $validatedData['user_id'],
                'staff_user_id' => Auth::user()->id,
                'total_amount' => $validatedData['total_amount'],
                'paid_amount' => 0,
                'status' => 'pending',
                'order_id' => $order->id,
                'note' => $validatedData['note'],
                'created_by_user_id' => Auth::user()->id,
            ]);

            DB::commit();

            return response()->json(['message' => 'Invoice created successfully', 'invoice' => $invoice], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error creating invoice', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
