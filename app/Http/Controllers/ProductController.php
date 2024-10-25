<?php

namespace App\Http\Controllers;

use App\Services\ProductService;
use App\Http\Requests\ProductRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;
use App\Models\Product;

class ProductController extends BaseController
{
    protected $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    /**
     * @OA\Get(
     *     path="/api/products",
     *     summary="Get product list",
     *     tags={"Products"},
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search for products",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="category",
     *         in="query",
     *         description="Filter by category",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of products per page",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", 
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="product_name", type="string"),
     *                     @OA\Property(property="category", type="object"),
     *                     @OA\Property(property="media", type="array",
     *                         @OA\Items(
     *                             @OA\Property(property="id", type="integer"),
     *                             @OA\Property(property="file_path", type="string"),
     *                             @OA\Property(property="type", type="string")
     *                         )
     *                     )
     *                 )
     *             ),
     *             @OA\Property(property="message", type="string", example="Products retrieved successfully")
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

    /**
     * @OA\Post(
     *     path="/api/products",
     *     summary="Tạo sản phẩm mới",
     *     tags={"Products"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/ProductRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Tạo thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/Product"),
     *             @OA\Property(property="message", type="string", example="Product created successfully")
     *         )
     *     )
     * )
     */
    public function store(ProductRequest $request)
    {
        $product = $this->productService->createProduct($request->validated());

        if ($request->expectsJson()) {
            return $this->respondWithJson($product, 'Product created successfully', 201);
        }

        return redirect()->route('products.index')->with('success', 'Sản phẩm đã được tạo thành công.');
    }

    /**
     * @OA\Get(
     *     path="/api/products/{id}",
     *     summary="Get product details",
     *     tags={"Products"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Product ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", 
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="product_name", type="string"),
     *                 @OA\Property(property="category", type="object"),
     *                 @OA\Property(property="media", type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="id", type="integer"),
     *                         @OA\Property(property="file_path", type="string"),
     *                         @OA\Property(property="type", type="string")
     *                     )
     *                 )
     *             ),
     *             @OA\Property(property="message", type="string", example="Product retrieved successfully")
     *         )
     *     )
     * )
     */
    public function show(string $id)
    {
        $product = $this->productService->getProductById($id);
        $product->load(['category', 'media', 'priceHistory', 'attributes']);

        if (request()->expectsJson()) {
            $product->media->transform(function ($media) {
                $media->full_url = $media->getFullUrlAttribute();
                return $media;
            });
            return $this->respondWithJson($product, 'Product retrieved successfully');
        }

        return $this->respondWithInertia('Products/Show', [
            'product' => $product
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/products/{id}",
     *     summary="Cập nhật sản phẩm",
     *     tags={"Products"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID sản phẩm",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/ProductRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Cập nhật thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/Product"),
     *             @OA\Property(property="message", type="string", example="Product updated successfully")
     *         )
     *     )
     * )
     */
    public function update(ProductRequest $request, string $id)
    {
        $product = $this->productService->updateProduct($id, $request->validated());

        if ($request->expectsJson()) {
            return $this->respondWithJson($product, 'Product updated successfully');
        }

        return redirect()->route('products.index')->with('success', 'Sản phẩm đã được cập nhật thành công.');
    }

    /**
     * @OA\Delete(
     *     path="/api/products/{id}",
     *     summary="Xóa sản phẩm",
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
     *         description="Xóa thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Product deleted successfully")
     *         )
     *     )
     * )
     */
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
}
