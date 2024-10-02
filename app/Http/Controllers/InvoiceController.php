<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Voucher;
use App\Models\PaymentMethod;
use App\Models\ProductCategory;
use App\Models\TreatmentCategory;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\User;
use App\Models\Product;
use App\Models\Treatment;

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
        $vouchers = Voucher::where('status', 'active')->get();
        $paymentMethods = PaymentMethod::all();
        $productCategories = ProductCategory::all();
        $treatmentCategories = TreatmentCategory::all();

        return Inertia::render('Invoice/InvoiceCreate', [
            'vouchers' => $vouchers,
            'paymentMethods' => $paymentMethods,
            'productCategories' => $productCategories,
            'treatmentCategories' => $treatmentCategories,
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


    public function searchProducts(Request $request)
    {
        $query = $request->input('query');
        $categoryId = $request->input('category_id');

        $products = Product::where('name', 'LIKE', "%{$query}%")
            ->when($categoryId, function ($q) use ($categoryId) {
                return $q->where('category_id', $categoryId);
            })
            ->limit(10)
            ->get(['id', 'name', 'price']);
        return response()->json($products);
    }

    public function searchTreatments(Request $request)
    {
        $query = $request->input('query');
        $categoryId = $request->input('category_id');

        $treatments = Treatment::where('name', 'LIKE', "%{$query}%")
            ->when($categoryId, function ($q) use ($categoryId) {
                return $q->where('category_id', $categoryId);
            })
            ->limit(10)
            ->get(['id', 'name', 'price']);
        return response()->json($treatments);
    }
}
