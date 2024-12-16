<?php

namespace App\Http\Controllers;

use App\Services\SearchService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\ProductCategory;
use App\Models\ServiceCategory;

/**
 * @OA\Tag(
 *     name="Search",
 *     description="API Endpoints for searching products and services"
 * )
 */
class SearchController extends BaseController
{
    protected $searchService;

    public function __construct(SearchService $searchService)
    {
        $this->searchService = $searchService;
    }

    /**
     * @OA\Get(
     *     path="/api/search",
     *     summary="Search for products and services",
     *     description="Search and filter products and services with various parameters",
     *     operationId="search",
     *     tags={"Search"},
     *     @OA\Parameter(
     *         name="query",
     *         in="query",
     *         description="Search keyword",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         description="Type of items to search (all, products, services)",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             enum={"all", "products", "services"},
     *             default="all"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Number of items per page",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             minimum=1,
     *             default=10
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="sort_by",
     *         in="query",
     *         description="Sort criteria",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             enum={"name_asc", "name_desc", "price_asc", "price_desc", "rating"}
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="min_price",
     *         in="query",
     *         description="Minimum price filter",
     *         required=false,
     *         @OA\Schema(
     *             type="number",
     *             minimum=0
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="max_price",
     *         in="query",
     *         description="Maximum price filter",
     *         required=false,
     *         @OA\Schema(
     *             type="number",
     *             minimum=0
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="category_id",
     *         in="query",
     *         description="Category ID filter",
     *         required=false,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful search results",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="products",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/Product"),
     *                 description="List of products (if type is 'all' or 'products')"
     *             ),
     *             @OA\Property(
     *                 property="services",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/Service"),
     *                 description="List of services (if type is 'all' or 'services')"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="The given data was invalid."
     *             ),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 example={
     *                     "type": {
     *                         "The selected type is invalid."
     *                     },
     *                     "min_price": {
     *                         "The min price must be at least 0."
     *                     }
     *                 }
     *             )
     *         )
     *     )
     * )
     */
    public function search(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'query' => 'nullable|string',
            'type' => 'nullable|in:all,products,services',
            'limit' => 'nullable|integer|min:1',
            'sort_by' => 'nullable|in:name_asc,name_desc,price_asc,price_desc,rating',
            'min_price' => 'nullable|numeric|min:0',
            'max_price' => 'nullable|numeric|min:0',
            'category_id' => 'nullable|integer'
        ]);

        if ($validator->fails()) {
            return $this->respondWithError($validator->errors(), 422);
        }

        $categories = [];
        $type = $request->get('type', 'all');
        
        if ($type === 'products' || $type === 'all') {
            $categories['product_categories'] = ProductCategory::select('id', 'name')->get();
        }
        
        if ($type === 'services' || $type === 'all') {
            $categories['service_categories'] = ServiceCategory::select('id', 'name')->get();
        }

        $results = $this->searchService->search($request->get('query', ''), [
            'type' => $type,
            'limit' => $request->get('limit', 10),
            'sort_by' => $request->get('sort_by'),
            'min_price' => $request->get('min_price'),
            'max_price' => $request->get('max_price'),
            'category_id' => $request->get('category_id')
        ]);

        return $this->respondWithJson([
            'results' => $results,
            'categories' => $categories
        ]);
    }
}
