<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductPriceHistory;
use App\Models\StockMovement;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProductService
{
    protected $mediaService;
    protected $priceHistoryService;
    protected $stockMovementService;

    public function __construct(
        MediaService $mediaService,
        ProductPriceHistoryService $priceHistoryService,
        StockMovementService $stockMovementService
    ) {
        $this->mediaService = $mediaService;
        $this->priceHistoryService = $priceHistoryService;
        $this->stockMovementService = $stockMovementService;
    }

    public function getProducts($request)
    {
        $query = Product::with([
            'category',
            'media',
            'translations',
            'ratings' => function ($query) {
                $query->where('status', 'approved')
                    ->where('rating_type', 'product');
            }
        ])
            ->withCount(['ratings as total_ratings' => function ($query) {
                $query->where('status', 'approved')
                    ->where('rating_type', 'product');
            }])
            ->withAvg(['ratings as average_rating' => function ($query) {
                $query->where('status', 'approved')
                    ->where('rating_type', 'product');
            }], 'stars');

        if (Auth::check()) {
            $userId = Auth::id();
            $query->each(function ($product) use ($userId) {
                $product->current_user_id = $userId;
            });
        }

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
        try {
            DB::beginTransaction();

            // Tạo sản phẩm
            $product = Product::create($data);
            Log::info('Đã tạo sản phẩm thành công:', [
                'product_id' => $product->id,
                'product_data' => $product->toArray()
            ]);

            // Tạo bản ghi stock movement ban đầu
            try {
                $stockMovement = $this->stockMovementService->createInitialMovement($product);
                Log::info('Đã tạo stock movement ban đầu:', [
                    'stock_movement_id' => $stockMovement->id,
                    'stock_movement_data' => $stockMovement->toArray()
                ]);
            } catch (\Exception $e) {
                Log::error('Lỗi khi tạo stock movement:', [
                    'product_id' => $product->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                throw $e;
            }

            // Xử lý hình ảnh nếu có
            if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
                Log::info('Bắt đầu xử lý hình ảnh đơn:', [
                    'product_id' => $product->id
                ]);
                $this->mediaService->create($product, $data['image'], 'image');
            }

            if (isset($data['images']) && is_array($data['images'])) {
                Log::info('Bắt đầu xử lý nhiều hình ảnh:', [
                    'product_id' => $product->id,
                    'image_count' => count($data['images'])
                ]);
                $this->mediaService->createMultiple($product, $data['images'], 'image');
            }

            DB::commit();
            Log::info('Hoàn thành tạo sản phẩm và các dữ liệu liên quan:', [
                'product_id' => $product->id
            ]);

            return $product;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi khi tạo sản phẩm:', [
                'error_message' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    public function getProductById($id, $userId = null)
    {
        $query = Product::with([
            'category',
            'media',
            'priceHistory',
            'attributes',
            'translations',
            'ratings' => function ($query) {
                $query->where('status', 'approved')
                    ->where('rating_type', 'product');
            }
        ])
            ->withCount(['ratings as total_ratings' => function ($query) {
                $query->where('status', 'approved')
                    ->where('rating_type', 'product');
            }])
            ->withAvg(['ratings as average_rating' => function ($query) {
                $query->where('status', 'approved')
                    ->where('rating_type', 'product');
            }], 'stars');

        $product = $query->findOrFail($id);

        // Set user_id vào product instance để dùng trong getIsFavoriteAttribute
        $product->current_user_id = $userId;

        return $product;
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

    /**
     * Adjust stock quantity
     */
    public function adjustStock(Product $product, int $quantity, string $type, string $note = null)
    {
        return $this->stockMovementService->createMovement($product, $quantity, $type, $note);
    }

    /**
     * Check if product has sufficient stock
     */
    public function checkStock(Product $product, int $quantity): bool
    {
        return $this->stockMovementService->hasSufficientStock($product, $quantity);
    }

    /**
     * Reduce stock when order is placed
     */
    public function reduceStock(Product $product, int $quantity, string $note = null)
    {
        if (!$this->checkStock($product, $quantity)) {
            throw new \Exception("Insufficient stock for product: {$product->name}");
        }

        return $this->adjustStock($product, $quantity, 'out', $note);
    }

    /**
     * Increase stock for returns or new inventory
     */
    public function increaseStock(Product $product, int $quantity, string $note = null)
    {
        return $this->adjustStock($product, $quantity, 'in', $note);
    }
}
