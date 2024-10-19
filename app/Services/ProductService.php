<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Database\Eloquent\Builder;

class ProductService
{
    public function getProducts($request)
    {
        $query = Product::with(['category', 'image']);

        if ($request->has('search')) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        if ($request->has('category')) {
            $query->where('category_id', $request->category);
        }

        if ($request->has('sort')) {
            $query->orderBy($request->sort, $request->input('direction', 'asc'));
        }

        return $query->paginate($request->input('per_page', 15));
    }

    public function getAllCategories()
    {
        return ProductCategory::all();
    }

    public function createProduct(array $data)
    {
        return Product::create($data);
    }

    public function getProductById($id)
    {
        return Product::with(['category', 'image', 'productDetail', 'attributes'])->findOrFail($id);
    }

    public function updateProduct($id, array $data)
    {
        $product = Product::findOrFail($id);
        $product->update($data);
        return $product;
    }

    public function deleteProduct($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();
    }

    public function searchProducts($searchTerm)
    {
        return Product::where('name', 'like', "%{$searchTerm}%")->get();
    }
}
