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
use Illuminate\Support\Facades\Log;
use App\Models\PaymentHistory;
use PayOS\PayOS;

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
    public function index(Request $request)
    {
        try {
            $filters = $request->only(['status', 'search']);
            $invoices = $this->invoiceService->getInvoices($filters);

            return $this->respondWithInertia('Invoice/InvoiceView', [
                'invoices' => $invoices,
                'filters' => $filters // Send current filters back to frontend
            ]);
        } catch (\Exception $e) {
            Log::error('Error in invoice index:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return $this->respondWithInertia('Invoice/InvoiceView', [
                'invoices' => [],
                'error' => 'Có lỗi xảy ra khi tải dữ liệu hóa đơn'
            ]);
        }
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

            $invoice = $this->invoiceService->createInvoice($validatedData);

            if ($request->expectsJson()) {
                return $this->respondWithJson($invoice->load(['user', 'order.orderItems']), 'Invoice created successfully', 201);
            }

            return redirect()->route('invoices.show', $invoice->id)
                ->with('success', 'Invoice created successfully');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return $this->respondWithJson(null, $e->getMessage(), 500);
            }
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, $id)
    {
        $invoice = Invoice::with([
            'user',
            'order.orderItems.product',
            'order.orderItems.service',
            'order.voucher',
            'paymentHistories.createdBy',
            'createdBy'
        ])->findOrFail($id);

        if ($request->expectsJson()) {
            return $this->respondWithJson($invoice, 'Invoice retrieved successfully');
        }

        Log::info('Invoice Data:', ['invoice' => $invoice->toArray()]);

        return $this->respondWithInertia('Invoice/InvoiceShow', [
            'invoice' => $invoice
        ]);
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

    public function processPayment(Request $request, Invoice $invoice)
    {
        try {
            $validatedData = $request->validate([
                'payment_amount' => [
                    'required',
                    'numeric',
                    'min:0',
                    'max:' . ($invoice->total_amount - $invoice->paid_amount)
                ],
                'payment_method' => 'required|string|in:cash,transfer',
                'payment_proof' => 'nullable|string',
                'note' => 'nullable|string',  // Thêm validation cho note
            ]);

            $invoice = $this->invoiceService->processPayment($invoice, $validatedData);

            if ($request->expectsJson()) {
                return $this->respondWithJson($invoice->fresh(['paymentHistories']), 'Payment processed successfully');
            }

            return redirect()->back()->with('success', 'Thanh toán thành công');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return $this->respondWithJson(null, $e->getMessage(), 500);
            }
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function cancel(Request $request, Invoice $invoice)
    {
        try {
            if (!in_array($invoice->status, ['pending', 'partial'])) {
                throw new \Exception('Không thể hủy hóa đơn này');
            }

            DB::beginTransaction();

            // Cập nhật trạng thái hóa đơn
            $oldStatus = $invoice->status;
            $invoice->update([
                'status' => Invoice::STATUS_CANCELLED
            ]);

            // Tạo lịch sử với payment_method là 'system'
            PaymentHistory::create([
                'invoice_id' => $invoice->id,
                'old_payment_status' => $oldStatus,
                'new_payment_status' => Invoice::STATUS_CANCELLED,
                'payment_amount' => 0,
                'payment_method' => 'system',
                'created_by_user_id' => Auth::id(),
                'note' => 'Hóa đơn đã bị hủy'
            ]);

            DB::commit();

            if ($request->expectsJson()) {
                return $this->respondWithJson($invoice->fresh(), 'Hóa đơn đã được hủy thành công');
            }

            return redirect()->back()->with('success', 'Hóa đơn đã được hủy thành công');
        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->expectsJson()) {
                return $this->respondWithJson(null, $e->getMessage(), 400);
            }
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function payWithPayOS(Request $request, Invoice $invoice)
    {
        try {
            // Kiểm tra trạng thái invoice
            if (!in_array($invoice->status, ['pending', 'partial'])) {
                throw new \Exception('Hóa đơn không hợp lệ để thanh toán');
            }

            $remainingAmount = $invoice->total_amount - $invoice->paid_amount;

            // Chuẩn bị data thanh toán
            $paymentData = [
                'orderCode' => 'INV' . $invoice->id . '_' . time(),
                'amount' => $remainingAmount,
                'description' => "Thanh toán hóa đơn #" . $invoice->id,
                'returnUrl' => config('app.url') . '/payment/test?invoice_id=' . $invoice->id,
                'cancelUrl' => config('app.url') . '/payment/test?invoice_id=' . $invoice->id . '&status=cancel',
            ];

            // Gọi PayOS service
            $payOS = app(PayOS::class);
            $response = $payOS->createPaymentLink($paymentData);

            if ($response && isset($response['checkoutUrl'])) {
                // Lưu lịch sử thanh toán
                PaymentHistory::create([
                    'invoice_id' => $invoice->id,
                    'old_payment_status' => $invoice->status,
                    'new_payment_status' => 'processing',
                    'payment_amount' => $remainingAmount,
                    'payment_method' => 'payos',
                    'created_by_user_id' => Auth::id(),
                    'note' => 'Khởi tạo thanh toán qua PayOS',
                    'payment_data' => json_encode([
                        'order_code' => $paymentData['orderCode'],
                        'checkout_url' => $response['checkoutUrl']
                    ])
                ]);

                return response()->json([
                    'success' => true,
                    'checkoutUrl' => $response['checkoutUrl']
                ]);
            }

            throw new \Exception('Không thể tạo link thanh toán');
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi xử lý thanh toán: ' . $e->getMessage()
            ], 500);
        }
    }
}
