<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;

class ProductService
{
    public function getProducts($request)
    {
        $query = Product::with(['category', 'media']);

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        if ($request->filled('sort')) {
            $direction = $request->input('direction', 'asc');
            $query->orderBy($request->sort, $direction);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        return $query;
    }

    public function getAllCategories()
    {
        return ProductCategory::all();
    }

    public function createProduct(array $data)
    {
        $product = Product::create($data);

        if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
            $path = $data['image']->store('products', 'public');
            $product->media()->create([
                'type' => 'image',
                'file_path' => $path
            ]);
        }

        return $product;
    }

    public function getProductById($id)
    {
        return Product::with(['category', 'media', 'priceHistory', 'attributes'])->findOrFail($id);
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
        return Product::where('product_name', 'like', "%{$searchTerm}%")->with('media')->get();
    }
}
