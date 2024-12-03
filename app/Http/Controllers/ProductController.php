<?php

namespace App\Http\Controllers;

use App\Services\ProductService;
use App\Http\Requests\ProductRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;
use App\Models\Product;
use App\Services\MediaService;
use Illuminate\Support\Facades\DB;

class ProductController extends BaseController
{
    protected $productService;
    protected $mediaService;
    public function __construct(ProductService $productService, MediaService $mediaService)
    {
        $this->productService = $productService;
        $this->mediaService = $mediaService;
    }

    /**
     * @OA\Get(
     *     path="/api/products",
     *     summary="Lấy danh sách sản phẩm",
     *     tags={"Products"},
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Tìm kiếm sản phẩm",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="category",
     *         in="query",
     *         description="Lọc theo danh mục",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Số sản phẩm trên mỗi trang",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="sort",
     *         in="query",
     *         description="Sắp xếp theo trường",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="direction",
     *         in="query",
     *         description="Hướng sắp xếp (asc hoặc desc)",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Products retrieved successfully"),
     *             @OA\Property(property="status_code", type="integer", example=200),
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(
     *                     property="data",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="name", type="string", example="Sample Product"),
     *                         @OA\Property(property="price", type="number", example=99.99),
     *                         @OA\Property(
     *                             property="media",
     *                             type="array",
     *                             @OA\Items(
     *                                 type="object",
     *                                 @OA\Property(property="id", type="integer", example=1),
     *                                 @OA\Property(property="type", type="string", example="image"),
     *                                 @OA\Property(property="file_path", type="string", example="products/sample.jpg"),
     *                                 @OA\Property(property="full_url", type="string", example="http://example.com/storage/products/sample.jpg")
     *                             )
     *                         )
     *                     )
     *                 ),
     *                 @OA\Property(property="first_page_url", type="string", example="http://example.com/api/products?page=1"),
     *                 @OA\Property(property="from", type="integer", example=1),
     *                 @OA\Property(property="last_page", type="integer", example=5),
     *                 @OA\Property(property="per_page", type="integer", example=15),
     *                 @OA\Property(property="total", type="integer", example=50)
     *             )
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 15);
        $products = $this->productService->getProducts($request)->paginate($perPage);

        $categories = $this->productService->getAllCategories();

        $filters = $request->only(['search', 'category', 'per_page', 'sort', 'direction']);

        // Log product data
        $this->logProductData($products);

        if ($request->wantsJson()) {
            return $this->respondWithJson($products, 'Products retrieved successfully');
        }

        return $this->respondWithInertia('Products/ProductView', [
            'products' => $products,
            'filters' => $filters,
            'categories' => $categories,
        ]);
    }

    public function store(ProductRequest $request)
    {
        try {
            // Validate basic product data
            $productData = $request->validated();

            // Create product
            $product = $this->productService->createProduct($productData);

            // Handle images if present
            if ($request->hasFile('images')) {
                $mediaItems = $this->mediaService->createMultiple($product, $request->file('images'), 'image');
            }

            if ($request->expectsJson()) {
                return $this->respondWithJson($product, 'Product created successfully', 201);
            }

            return redirect()->route('products.index')->with('success', 'Sản phẩm đã được tạo thành công.');
        } catch (\Exception $e) {
            Log::error('Product creation failed:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            if ($request->expectsJson()) {
                return $this->respondWithError('Không thể tạo sản phẩm: ' . $e->getMessage(), 500);
            }

            return back()->withErrors(['error' => 'Không thể tạo sản phẩm'])->withInput();
        }
    }

    /**
     * @OA\Get(
     *     path="/api/products/{id}",
     *     summary="Lấy chi tiết sản phẩm",
     *     tags={"Products"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID sản phẩm",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Product retrieved successfully"),
     *             @OA\Property(property="status_code", type="integer", example=200),
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="FAITH Members Club Face Lamela Veil EX Cleansing"),
     *                 @OA\Property(property="price", type="string", example="1000000.00"),
     *                 @OA\Property(property="category_id", type="integer", example=1),
     *                 @OA\Property(property="quantity", type="integer", example=100),
     *                 @OA\Property(property="brand_description", type="string", example="FAITH"),
     *                 @OA\Property(property="usage", type="string", example="Lấy một lượng vừa đủ (3-4 lần bơm) ra lòng bàn tay, thoa đều. Massage nhẹ nhàng lên da khô để hòa tan lớp trang điểm và bụi bẩn. Rửa sạch lại với nước ấm."),
     *                 @OA\Property(property="benefits", type="string", example="Kết cấu sản phẩm mềm mượt, dễ tán đều, mang đến làn da sạch thoáng, ẩm mịn."),
     *                 @OA\Property(property="key_ingredients", type="string", example="Gelatin Collagen*1"),
     *                 @OA\Property(property="ingredients", type="string", example="Water, Coconut Oil Fatty Acid PEG-7 Glyceryl, BG, Polysorbate 60, Pentylene Glycol, Glycerin, Water-Soluble Collagen, Sodium Hyaluronate, Hydrolyzed Elastin, Lactobacillus/Pear Juice Ferment Filtrate, Galactoarabinan, PCA-Na, Rosa Damascena Flower Water, Sodium Lauroyl Glutamate Lysine, Ectoin, Magnesium Ascorbyl Phosphate, Houttuynia Cordata Extract, Aloe Barbadensis Leaf Extract, Rosmarinus Officinalis (Rosemary) Leaf Extract, Eugenia Caryophyllus (Clove) Flower Extract, Arginine, Tocopherol, Ceramide 3, Hydrogenated Lecithin, Cholesterol, Carbomer, Potassium Hydroxide"),
     *                 @OA\Property(property="directions", type="string", example="Sử dụng hàng ngày, sáng và tối"),
     *                 @OA\Property(property="storage_instructions", type="string", example="Bảo quản nơi khô ráo, thoáng mát, tránh ánh nắng trực tiếp"),
     *                 @OA\Property(property="product_notes", type="string", example="Phù hợp cho mọi loại da"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", nullable=true),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", nullable=true),
     *                 @OA\Property(property="deleted_at", type="string", format="date-time", nullable=true),
     *                 @OA\Property(
     *                     property="category",
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="category_name", type="string", example="Làm sạch"),
     *                     @OA\Property(property="parent_id", type="integer", nullable=true),
     *                     @OA\Property(property="created_at", type="string", format="date-time", nullable=true),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", nullable=true),
     *                     @OA\Property(property="deleted_at", type="string", format="date-time", nullable=true)
     *                 ),
     *                 @OA\Property(
     *                     property="media",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="type", type="string", example="image"),
     *                         @OA\Property(property="file_path", type="string", example="/images/products/cleansing.jpg"),
     *                         @OA\Property(property="mediable_type", type="string", example="product"),
     *                         @OA\Property(property="mediable_id", type="integer", example=1),
     *                         @OA\Property(property="created_at", type="string", format="date-time", nullable=true),
     *                         @OA\Property(property="updated_at", type="string", format="date-time", nullable=true),
     *                         @OA\Property(property="deleted_at", type="string", format="date-time", nullable=true),
     *                         @OA\Property(property="full_url", type="string", example="http://localhost:8000/storage//images/products/cleansing.jpg")
     *                     )
     *                 ),
     *                 @OA\Property(property="price_history", type="array", @OA\Items(type="object")),
     *                 @OA\Property(property="attributes", type="array", @OA\Items(type="object"))
     *             )
     *         )
     *     )
     * )
     */
    public function show(Request $request, Product $product)
    {
        $userId = $request->query('user_id');
        $product = $this->productService->getProductById($product->id, $userId);
        $product->load(['category', 'media', 'priceHistory', 'attributes']);

        if (request()->expectsJson()) {
            return $this->respondWithJson($product, 'Product retrieved successfully');
        }

        return $this->respondWithInertia('Products/Show', [
            'product' => $product,
        ]);
    }

    public function update(ProductRequest $request, string $id)
    {
        try {
            $product = $this->productService->updateProduct($id, $request->validated());

            if ($request->wantsJson()) {
                return $this->respondWithJson([
                    'product' => $product,
                    'message' => 'Cập nhật sản phẩm thành công'
                ]);
            }

            return back()->with('success', 'Sản phẩm đã được cập nhật thành công');
        } catch (\Exception $e) {
            Log::error('Product update failed:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            if ($request->wantsJson()) {
                return $this->respondWithError('Không thể cập nhật sản phẩm: ' . $e->getMessage(), 500);
            }

            return back()->withErrors(['error' => 'Không thể cập nhật sản phẩm'])->withInput();
        }
    }

    public function destroy(string $id)
    {
        $this->productService->deleteProduct($id);

        if (request()->expectsJson()) {
            return $this->respondWithJson(null, 'Product deleted successfully');
        }

        return redirect()->route('products.index')->with('success', 'Sản phẩm đã được xóa thành công.');
    }

    /**
     * @OA\Get(
     *     path="/api/products/search",
     *     summary="Search products",
     *     tags={"Products"},
     *     @OA\Parameter(
     *         name="query",
     *         in="query",
     *         description="Search query",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", 
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="product_name", type="string"),
     *                     @OA\Property(property="media", type="array",
     *                         @OA\Items(
     *                             @OA\Property(property="id", type="integer"),
     *                             @OA\Property(property="file_path", type="string"),
     *                             @OA\Property(property="type", type="string")
     *                         )
     *                     )
     *                 )
     *             ),
     *             @OA\Property(property="message", type="string", example="Products searched successfully")
     *         )
     *     )
     * )
     */
    public function searchProducts(Request $request)
    {
        $query = $request->get('query');

        $products = Product::where('name', 'LIKE', "%{$query}%")
            ->where('quantity', '>', 0)
            ->take(10)
            ->get(['id', 'name', 'price']);

        return response()->json([
            'data' => $products
        ]);
    }

    /**
     * Log product data for debugging
     *
     * @param \Illuminate\Pagination\LengthAwarePaginator $products
     */
    private function logProductData($products)
    {
        $logData = $products->map(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'media_loaded' => $product->relationLoaded('media'),
                'media_count' => $product->media->count(),
                'media' => $product->media->map(function ($media) {
                    return [
                        'id' => $media->id,
                        'file_path' => $media->file_path,
                        'full_url' => $media->getFullUrlAttribute(),
                        'file_exists' => $media->fileExists(),
                    ];
                })->toArray(),
            ];
        })->toArray();

        Log::channel('product_debug')->info('Product data for view:', $logData);
    }

    /**
     * Upload images for a product
     * 
     * @param Request $request
     * @param Product $product
     * @return \Illuminate\Http\JsonResponse
     */
    public function uploadImages(Request $request, Product $product)
    {
        try {
            Log::channel('product_debug')->info('Image upload started for product:', [
                'product_id' => $product->id,
                'has_files' => $request->hasFile('images'),
                'files_count' => $request->hasFile('images') ? count($request->file('images')) : 0
            ]);

            if (!$request->hasFile('images')) {
                return $this->respondWithError('No images provided', 400);
            }

            $mediaItems = $this->mediaService->createMultiple($product, $request->file('images'), 'image');

            Log::channel('product_debug')->info('Images uploaded successfully:', [
                'media_items_count' => count($mediaItems),
                'media_items' => collect($mediaItems)->map(fn($item) => [
                    'id' => $item->id,
                    'path' => $item->file_path,
                    'full_url' => $item->getFullUrlAttribute()
                ])
            ]);

            return $this->respondWithJson([
                'media' => $mediaItems,
                'message' => 'Images uploaded successfully'
            ]);
        } catch (\Exception $e) {
            Log::channel('product_debug')->error('Image upload failed:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return $this->respondWithError('Failed to upload images: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get translations for a product
     */
    public function getTranslations(Product $product)
    {
        try {
            $translations = [];

            // Lấy tất cả bản dịch của sản phẩm
            $translationsFromDb = DB::table('translations')
                ->where('translatable_id', $product->id)
                ->where('translatable_type', Product::class)
                ->select('language', 'field', 'value')
                ->get();

            // Tổ chức lại dữ liệu theo cấu trúc language -> field -> value
            foreach ($translationsFromDb as $translation) {
                if (!isset($translations[$translation->language])) {
                    $translations[$translation->language] = [];
                }
                $translations[$translation->language][$translation->field] = $translation->value;
            }

            // Log để debug
            Log::info('Product translations:', [
                'product_id' => $product->id,
                'translations' => $translations
            ]);

            return $this->respondWithJson($translations, 'Translations retrieved successfully');
        } catch (\Exception $e) {
            Log::error('Error getting translations:', [
                'product_id' => $product->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return $this->respondWithError('Failed to get translations: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Save translations for a product
     */
    public function saveTranslations(Request $request, Product $product)
    {
        try {
            DB::beginTransaction();

            // Kiểm tra và lấy danh sách các trường có thể dịch từ model
            $translatableFields = $product->getTranslatableAttributes();

            foreach ($request->translations as $language => $fields) {
                foreach ($fields as $field => $value) {
                    // Kiểm tra field có trong danh sách translatable không
                    if (in_array($field, $translatableFields)) {
                        $product->setTranslation($field, $language, $value);
                    }
                }
            }

            $product->save();
            DB::commit();

            return $this->respondWithJson(null, 'Translations saved successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Translation save error:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->respondWithError('Failed to save translations: ' . $e->getMessage(), 500);
        }
    }
}
