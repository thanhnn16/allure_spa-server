<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductPriceHistory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class ProductService
{
    protected $mediaService;
    protected $priceHistoryService;

    public function __construct(
        MediaService $mediaService,
        ProductPriceHistoryService $priceHistoryService
    ) {
        $this->mediaService = $mediaService;
        $this->priceHistoryService = $priceHistoryService;
    }

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
            $this->mediaService->create($product, $data['image'], 'image');
        }

        if (isset($data['images']) && is_array($data['images'])) {
            $this->mediaService->createMultiple($product, $data['images'], 'image');
        }

        return $product;
    }

    public function getProductById($id)
    {
        return Product::with([
            'category', 
            'media',
            'priceHistory' => function($query) {
                $query->orderBy('effective_from', 'desc');
            },
            'attributes',
            'ratings' => function($query) {
                $query->where('status', 'approved')
                      ->where('rating_type', 'product');
            }
        ])
        ->withCount(['ratings as total_ratings' => function($query) {
            $query->where('status', 'approved')
                  ->where('rating_type', 'product');
        }])
        ->withAvg(['ratings as average_rating' => function($query) {
            $query->where('status', 'approved')
                  ->where('rating_type', 'product');
        }], 'stars')
        ->findOrFail($id);
    }

    public function updateProduct($id, array $data)
    {
        try {
            DB::beginTransaction();
            
            $product = Product::findOrFail($id);
            $oldPrice = $product->price;
            
            // Update product
            $product->update($data);

            // If price has changed, create price history record
            if (isset($data['price']) && $oldPrice != $data['price']) {
                // Close previous price history record
                ProductPriceHistory::where('product_id', $product->id)
                    ->whereNull('effective_to')
                    ->update(['effective_to' => now()]);

                // Create new price history record
                ProductPriceHistory::create([
                    'product_id' => $product->id,
                    'price' => $data['price'],
                    'effective_from' => now(),
                ]);
            }

            // Handle image uploads if present
            if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
                $this->mediaService->create($product, $data['image'], 'image');
            }

            if (isset($data['images']) && is_array($data['images'])) {
                $this->mediaService->createMultiple($product, $data['images'], 'image');
            }

            DB::commit();
            return $product;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
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
