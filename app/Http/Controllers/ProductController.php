<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Brand;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductController extends Controller
{
    /**
     * Hiển thị danh sách sản phẩm.
     */
    public function index(Request $request)
    {
        $query = Product::with(['category', 'brand']);

        // Áp dụng bộ lọc
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }
        if ($request->filled('brand')) {
            $query->where('brand_id', $request->brand);
        }
        if ($request->filled('search')) {
            $query->where('product_name', 'like', '%' . $request->search . '%');
        }

        // Phân trang
        $perPage = $request->input('per_page', 10);
        $products = $query->paginate($perPage);

        $categories = ProductCategory::all();
        $brands = Brand::all();

        return Inertia::render('Products/ProductView', [
            'products' => $products,
            'categories' => $categories,
            'brands' => $brands,
            'filters' => $request->only(['category', 'brand', 'search', 'per_page']),
        ]);
    }

    /**
     * Hiển thị danh sách sản phẩm faith.
     */
    public function faith()
    {
        // Triển khai logic cho sản phẩm faith ở đây
    }

    /**
     * Lưu trữ sản phẩm mới được tạo.
     */
    public function store(Request $request)
    {
        // Triển khai logic tạo sản phẩm mới ở đây
    }

    /**
     * Hiển thị thông tin chi tiết của sản phẩm.
     */
    public function show(Product $product)
    {
        $product->load(['category', 'brand', 'details', 'attributes']);
        return Inertia::render('Products/ProductDetail', [
            'product' => $product
        ]);
    }

    /**
     * Cập nhật thông tin sản phẩm.
     */
    public function update(Request $request, Product $product)
    {
        // Triển khai logic cập nhật sản phẩm ở đây
    }

    /**
     * Xóa sản phẩm khỏi hệ thống.
     */
    public function destroy(Product $product)
    {
        // Triển khai logic xóa sản phẩm ở đây
    }
}
