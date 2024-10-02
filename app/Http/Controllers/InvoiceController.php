<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\User;
use App\Models\Voucher;
use App\Models\PaymentMethod;
use App\Models\CartItemType;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InvoiceController extends Controller
{
    public function index()
    {
        $invoices = Invoice::with('user')->orderBy('created_at', 'desc')->get();
        return Inertia::render('Invoice/InvoiceView', [
            'invoices' => $invoices,
        ]);
    }

    public function create()
    {
        $users = User::all();
        $vouchers = Voucher::where('status', 'active')->get();
        $paymentMethods = PaymentMethod::all();
        $cartItemTypes = CartItemType::all();

        return Inertia::render('Invoice/InvoiceCreate', [
            'users' => $users,
            'vouchers' => $vouchers,
            'paymentMethods' => $paymentMethods,
            'cartItemTypes' => $cartItemTypes,
        ]);
    }

    public function store(Request $request)
    {
        // Xử lý lưu hóa đơn vào database
        // Validation và lưu dữ liệu
    }

    public function show(Invoice $invoice)
    {
        $invoice->load(['user', 'voucher', 'paymentMethod', 'orderItems']);
        return Inertia::render('Invoice/InvoiceDetail', [
            'invoice' => $invoice,
        ]);
    }
}
