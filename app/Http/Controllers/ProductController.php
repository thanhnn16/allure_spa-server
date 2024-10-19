<?php

namespace App\Http\Controllers;

use App\Services\ProductService;
use App\Http\Requests\ProductRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;

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
     *     @OA\Response(
     *         response=200,
     *         description="Thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Product")),
     *             @OA\Property(property="message", type="string", example="Products retrieved successfully")
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $products = $this->productService->getProducts($request);

        if ($request->expectsJson()) {
            return $this->respondWithJson($products, 'Products retrieved successfully');
        }

        return $this->respondWithInertia('Products/Index', [
            'products' => $products,
            'filters' => $request->all(['search', 'category', 'per_page', 'sort', 'direction']),
            'categories' => $this->productService->getAllCategories(),
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
     *     summary="Lấy thông tin chi tiết sản phẩm",
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
     *             @OA\Property(property="data", ref="#/components/schemas/Product"),
     *             @OA\Property(property="message", type="string", example="Product retrieved successfully")
     *         )
     *     )
     * )
     */
    public function show(string $id)
    {
        $product = $this->productService->getProductById($id);

        if (request()->expectsJson()) {
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

    public function searchProducts(Request $request)
    {
        $products = $this->productService->searchProducts($request->input('query'));

        return $this->respondWithJson($products, 'Products searched successfully');
    }
}
