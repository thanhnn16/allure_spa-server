<?php

namespace App\Http\Controllers;

use App\Services\RatingService;
use Illuminate\Http\Request;
use App\Http\Requests\CreateRatingRequest;
use OpenApi\Annotations as OA;
use App\Models\Rating;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

/**
 * @OA\Schema(
 *     schema="Rating",
 *     title="Đánh giá",
 *     @OA\Property(property="id", type="integer"),
 *     @OA\Property(property="user_id", type="string", format="uuid"),
 *     @OA\Property(property="order_item_id", type="integer"),
 *     @OA\Property(property="rating_type", type="string", enum={"product", "service"}),
 *     @OA\Property(property="item_id", type="integer"),
 *     @OA\Property(property="stars", type="integer", minimum=1, maximum=5),
 *     @OA\Property(property="comment", type="string", nullable=true),
 *     @OA\Property(property="status", type="string", enum={"pending", "approved", "rejected"}),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time"),
 *     @OA\Property(
 *         property="media",
 *         type="array",
 *         @OA\Items(
 *             @OA\Property(property="id", type="integer"),
 *             @OA\Property(property="type", type="string"),
 *             @OA\Property(property="url", type="string")
 *         )
 *     ),
 *     @OA\Property(property="user", ref="#/components/schemas/User"),
 *     @OA\Property(property="order_item", ref="#/components/schemas/OrderItem")
 * )
 */

/**
 * @OA\Schema(
 *     schema="PaginatedRatingResponse",
 *     @OA\Property(property="current_page", type="integer"),
 *     @OA\Property(
 *         property="data",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/Rating")
 *     ),
 *     @OA\Property(property="first_page_url", type="string"),
 *     @OA\Property(property="from", type="integer"),
 *     @OA\Property(property="last_page", type="integer"),
 *     @OA\Property(property="last_page_url", type="string"),
 *     @OA\Property(property="next_page_url", type="string", nullable=true),
 *     @OA\Property(property="path", type="string"),
 *     @OA\Property(property="per_page", type="integer"),
 *     @OA\Property(property="prev_page_url", type="string", nullable=true),
 *     @OA\Property(property="to", type="integer"),
 *     @OA\Property(property="total", type="integer")
 * )
 */

/**
 * @OA\Tag(
 *     name="Ratings",
 *     description="API Endpoints để quản lý đánh giá"
 * )
 */
class RatingController extends BaseController
{
    protected $ratingService;

    public function __construct(RatingService $ratingService)
    {
        $this->ratingService = $ratingService;
    }

    /**
     * @OA\Get(
     *     path="/api/ratings",
     *     summary="Lấy danh sách tất cả đánh giá",
     *     description="Trả về danh sách tất cả đánh giá với phân trang",
     *     operationId="getAllRatings",
     *     tags={"Ratings"},
     *     @OA\Parameter(
     *         name="sort_by",
     *         in="query",
     *         description="Sắp xếp theo trường (ví dụ: stars)",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="sort_direction",
     *         in="query",
     *         description="Hướng sắp xếp (asc hoặc desc)",
     *         required=false,
     *         @OA\Schema(type="string", enum={"asc", "desc"})
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Số lượng kết quả trên mỗi trang",
     *         required=false,
     *         @OA\Schema(type="integer", default=15)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Danh sách đánh giá"),
     *             @OA\Property(property="status_code", type="integer", example=200),
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(
     *                     property="data",
     *                     type="array",
     *                     @OA\Items(ref="#/components/schemas/Rating")
     *                 ),
     *                 @OA\Property(property="first_page_url", type="string", example="http://localhost/api/ratings?page=1"),
     *                 @OA\Property(property="from", type="integer", example=1),
     *                 @OA\Property(property="last_page", type="integer", example=1),
     *                 @OA\Property(property="last_page_url", type="string", example="http://localhost/api/ratings?page=1"),
     *                 @OA\Property(property="next_page_url", type="string", example=null),
     *                 @OA\Property(property="path", type="string", example="http://localhost/api/ratings"),
     *                 @OA\Property(property="per_page", type="integer", example=15),
     *                 @OA\Property(property="prev_page_url", type="string", example=null),
     *                 @OA\Property(property="to", type="integer", example=15),
     *                 @OA\Property(property="total", type="integer", example=15)
     *             )
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $ratings = $this->ratingService->getAllRatings($request->all());
        Log::info('ratings', [$ratings]);
        if ($request->wantsJson()) {
            return $this->respondWithJson($ratings, 'Danh sách đánh giá');
        }
        return Inertia::render('Ratings/Index', [
            'ratings' => $ratings
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/products/{productId}/ratings",
     *     summary="Lấy danh sách đánh giá cho một sản phẩm",
     *     description="Trả về danh sách đánh giá cho một sản phẩm cụ thể với phân trang",
     *     operationId="getProductRatings",
     *     tags={"Ratings"},
     *     @OA\Parameter(
     *         name="productId",
     *         in="path",
     *         description="ID của sản phẩm",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="sort_by",
     *         in="query",
     *         description="Sắp xếp theo trường (ví dụ: stars)",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="sort_direction",
     *         in="query",
     *         description="Hướng sắp xếp (asc hoặc desc)",
     *         required=false,
     *         @OA\Schema(type="string", enum={"asc", "desc"})
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Số lượng kết quả trên mỗi trang",
     *         required=false,
     *         @OA\Schema(type="integer", default=15)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Đánh giá cho sản phẩm"),
     *             @OA\Property(property="status_code", type="integer", example=200),
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(
     *                     property="data",
     *                     type="array",
     *                     @OA\Items(ref="#/components/schemas/Rating")
     *                 ),
     *                 @OA\Property(property="first_page_url", type="string", example="http://localhost/api/products/1/ratings?page=1"),
     *                 @OA\Property(property="from", type="integer", example=1),
     *                 @OA\Property(property="last_page", type="integer", example=1),
     *                 @OA\Property(property="last_page_url", type="string", example="http://localhost/api/products/1/ratings?page=1"),
     *                 @OA\Property(property="next_page_url", type="string", example=null),
     *                 @OA\Property(property="path", type="string", example="http://localhost/api/products/1/ratings"),
     *                 @OA\Property(property="per_page", type="integer", example=15),
     *                 @OA\Property(property="prev_page_url", type="string", example=null),
     *                 @OA\Property(property="to", type="integer", example=15),
     *                 @OA\Property(property="total", type="integer", example=15)
     *             )
     *         )
     *     )
     * )
     */
    public function getProductRatings(Request $request, $productId)
    {
        $ratings = $this->ratingService->getRatingsByProduct($productId, $request->all());
        return $this->respondWithJson($ratings, 'Đánh giá cho sản phẩm');
    }

    /**
     * @OA\Get(
     *     path="/api/services/{serviceId}/ratings",
     *     summary="Lấy danh sách đánh giá cho một liệu trình",
     *     description="Trả về danh sách đánh giá cho một liệu trình cụ thể với phân trang",
     *     operationId="getServiceRatings",
     *     tags={"Ratings"},
     *     @OA\Parameter(
     *         name="serviceId",
     *         in="path",
     *         description="ID của liệu trình",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="sort_by",
     *         in="query",
     *         description="Sắp xếp theo trường (ví dụ: stars)",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="sort_direction",
     *         in="query",
     *         description="Hướng sắp xếp (asc hoặc desc)",
     *         required=false,
     *         @OA\Schema(type="string", enum={"asc", "desc"})
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Số lượng kết quả trên mỗi trang",
     *         required=false,
     *         @OA\Schema(type="integer", default=15)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Đánh giá cho liệu trình"),
     *             @OA\Property(property="status_code", type="integer", example=200),
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(
     *                     property="data",
     *                     type="array",
     *                     @OA\Items(ref="#/components/schemas/Rating")
     *                 ),
     *                 @OA\Property(property="first_page_url", type="string", example="http://localhost/api/services/1/ratings?page=1"),
     *                 @OA\Property(property="from", type="integer", example=1),
     *                 @OA\Property(property="last_page", type="integer", example=1),
     *                 @OA\Property(property="last_page_url", type="string", example="http://localhost/api/services/1/ratings?page=1"),
     *                 @OA\Property(property="next_page_url", type="string", example=null),
     *                 @OA\Property(property="path", type="string", example="http://localhost/api/services/1/ratings"),
     *                 @OA\Property(property="per_page", type="integer", example=15),
     *                 @OA\Property(property="prev_page_url", type="string", example=null),
     *                 @OA\Property(property="to", type="integer", example=15),
     *                 @OA\Property(property="total", type="integer", example=15)
     *             )
     *         )
     *     )
     * )
     */
    public function getServiceRatings(Request $request, $serviceId)
    {
        $ratings = $this->ratingService->getRatingsByService($serviceId, $request->all());
        return $this->respondWithJson($ratings, 'Đánh giá cho liệu trình');
    }

    /**
     * @OA\Post(
     *     path="/api/ratings",
     *     summary="Tạo một đánh giá mới",
     *     description="Tạo một đánh giá mới cho sản phẩm hoặc liệu trình",
     *     operationId="createRating",
     *     tags={"Ratings"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"user_id", "rating_type", "item_id", "stars", "status"},
     *             @OA\Property(property="user_id", type="integer", example=1),
     *             @OA\Property(property="rating_type", type="string", enum={"service", "product"}, example="product"),
     *             @OA\Property(property="item_id", type="integer", example=1),
     *             @OA\Property(property="stars", type="integer", example=5),
     *             @OA\Property(property="comment", type="string", example="Sản phẩm rất tốt!"),
     *             @OA\Property(property="image_id", type="integer", example=null),
     *             @OA\Property(property="video_id", type="integer", example=null),
     *             @OA\Property(property="status", type="string", enum={"pending", "approved", "rejected"}, example="pending")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Đánh giá đã được tạo thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Đánh giá đã được tạo thành công"),
     *             @OA\Property(property="status_code", type="integer", example=201),
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/Rating")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Dữ liệu không hợp lệ",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function store(CreateRatingRequest $request)
    {
        $rating = $this->ratingService->createRating($request->validated());
        return $this->respondWithJson($rating, 'Đánh giá đã được tạo thành công', 201);
    }

    /**
     * @OA\Get(
     *     path="/api/products/{productId}/approved-ratings",
     *     summary="Lấy danh sách đánh giá đã duyệt của sản phẩm",
     *     tags={"Ratings"},
     *     @OA\Parameter(
     *         name="productId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Rating")),
     *             @OA\Property(property="meta", type="object")
     *         )
     *     )
     * )
     */
    public function getApprovedProductRatings($productId)
    {
        $ratings = $this->ratingService->getRatingsByProduct($productId, [
            'status' => 'approved',
            'per_page' => 10
        ]);
        return $this->respondWithJson($ratings, 'Danh sách đánh giá đã duyệt của sản phẩm');
    }

    /**
     * @OA\Get(
     *     path="/api/services/{serviceId}/approved-ratings",
     *     summary="Lấy danh sách đánh giá đã duyệt của dịch vụ",
     *     tags={"Ratings"},
     *     @OA\Parameter(
     *         name="serviceId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Rating")),
     *             @OA\Property(property="meta", type="object")
     *         )
     *     )
     * )
     */
    public function getApprovedServiceRatings($serviceId)
    {
        $ratings = $this->ratingService->getRatingsByService($serviceId, [
            'status' => 'approved',
            'per_page' => 10
        ]);
        return $this->respondWithJson($ratings, 'Danh sách đánh giá đã duyệt của dịch vụ');
    }

    /**
     * @OA\Post(
     *     path="/api/ratings/from-order",
     *     summary="Tạo đánh giá từ đơn hàng",
     *     tags={"Ratings"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"rating_type", "item_id", "stars"},
     *             @OA\Property(property="rating_type", type="string", enum={"product", "service"}),
     *             @OA\Property(property="item_id", type="integer"),
     *             @OA\Property(property="stars", type="integer", minimum=1, maximum=5),
     *             @OA\Property(property="comment", type="string"),
     *             @OA\Property(
     *                 property="images",
     *                 type="array",
     *                 @OA\Items(
     *                     type="string",
     *                     format="binary"
     *                 ),
     *                 description="Tối đa 5 ảnh, mỗi ảnh không quá 5MB"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Đánh giá đã được tạo thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Đánh giá đã được tạo thành công"),
     *             @OA\Property(property="status_code", type="integer", example=201),
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 allOf={
     *                     @OA\Schema(ref="#/components/schemas/Rating"),
     *                     @OA\Schema(
     *                         @OA\Property(
     *                             property="media",
     *                             type="array",
     *                             @OA\Items(ref="#/components/schemas/Media")
     *                         )
     *                     )
     *                 }
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Lỗi xác thực hoặc quyền hạn",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Bạn chỉ có thể đánh giá sản phẩm/dịch vụ đã mua"),
     *             @OA\Property(property="status_code", type="integer", example=403),
     *             @OA\Property(property="success", type="boolean", example=false)
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Dữ liệu không hợp lệ",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="images",
     *                     type="array",
     *                     @OA\Items(type="string", example="Tối đa chỉ được gửi 5 ảnh")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function storeFromOrder(Request $request)
    {
        $validated = $request->validate([
            'rating_type' => 'required|in:product,service',
            'item_id' => 'required|integer',
            'stars' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string',
            'images' => 'nullable|array|max:5',
            'images.*' => 'image|mimes:jpeg,png,jpg|max:5120'
        ]);

        try {
            $rating = $this->ratingService->createRatingFromOrder(
                array_merge($validated, ['images' => $request->file('images')]),
                Auth::id()
            );
            
            return $this->respondWithJson($rating->load(['media']), 'Đánh giá đã được tạo thành công', 201);
        } catch (\Exception $e) {
            return $this->respondWithJson(null, $e->getMessage(), 403);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/ratings/{id}",
     *     summary="Cập nhật đánh giá",
     *     tags={"Ratings"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="stars", type="integer", minimum=1, maximum=5),
     *             @OA\Property(property="comment", type="string"),
     *             @OA\Property(
     *                 property="images",
     *                 type="array",
     *                 @OA\Items(
     *                     type="string",
     *                     format="binary"
     *                 ),
     *                 description="Tối đa 5 ảnh, mỗi ảnh không quá 5MB. Nếu gửi ảnh mới, tất cả ảnh cũ sẽ bị xóa"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Đánh giá đã được cập nhật thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Đánh giá đã được cập nhật thành công"),
     *             @OA\Property(property="status_code", type="integer", example=200),
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 allOf={
     *                     @OA\Schema(ref="#/components/schemas/Rating"),
     *                     @OA\Schema(
     *                         @OA\Property(
     *                             property="media",
     *                             type="array",
     *                             @OA\Items(ref="#/components/schemas/Media")
     *                         )
     *                     )
     *                 }
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Không có quyền cập nhật",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Bạn không có quyền sửa đánh giá này"),
     *             @OA\Property(property="status_code", type="integer", example=403),
     *             @OA\Property(property="success", type="boolean", example=false)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Không tìm thấy đánh giá",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Not Found"),
     *             @OA\Property(property="status_code", type="integer", example=404),
     *             @OA\Property(property="success", type="boolean", example=false)
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Dữ liệu không hợp lệ",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="images",
     *                     type="array",
     *                     @OA\Items(type="string", example="Tối đa chỉ được gửi 5 ảnh")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        $rating = Rating::findOrFail($id);

        if ($rating->user_id !== Auth::user()->id && Auth::user()->role !== 'admin') {
            return $this->respondWithJson(null, 'Bạn không có quyền sửa đánh giá này', 403);
        }

        $validated = $request->validate([
            'stars' => 'sometimes|required|integer|min:1|max:5',
            'comment' => 'nullable|string',
            'images' => 'nullable|array|max:5',
            'images.*' => 'image|mimes:jpeg,png,jpg|max:5120'
        ]);

        $rating = $this->ratingService->updateRating($rating, array_merge(
            $validated,
            ['images' => $request->file('images')]
        ));

        return $this->respondWithJson($rating, 'Đánh giá đã được cập nhật thành công');
    }

    /**
     * @OA\Post(
     *     path="/api/ratings/{id}/approve",
     *     summary="Duyệt đánh giá",
     *     tags={"Ratings"},
     *     @OA\Response(response=200, description="Successful operation")
     * )
     */
    public function approve($id)
    {
        $rating = Rating::findOrFail($id);

        if (Auth::user()->role !== 'admin') {
            return $this->respondWithJson(null, 'Bạn không có quyền duyệt đánh giá', 403);
        }

        $this->ratingService->approveRating($rating);
        return $this->respondWithJson($rating, 'Đánh giá đã được duyệt');
    }

    /**
     * @OA\Post(
     *     path="/api/ratings/{id}/reject",
     *     summary="Từ chối đánh giá",
     *     tags={"Ratings"},
     *     @OA\Response(response=200, description="Successful operation")
     * )
     */
    public function reject($id)
    {
        $rating = Rating::findOrFail($id);

        if (Auth::user()->role !== 'admin') {
            return $this->respondWithJson(null, 'Bạn không có quyền từ chối đánh giá', 403);
        }

        $this->ratingService->rejectRating($rating);
        return $this->respondWithJson($rating, 'Đánh giá đã bị từ chối');
    }

    /**
     * @OA\Patch(
     *     path="/api/ratings/{id}/status",
     *     summary="Cập nhật trạng thái đánh giá",
     *     tags={"Ratings"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", enum={"approved", "rejected"}, example="approved")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Cập nhật trạng thái thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Cập nhật trạng thái thành công"),
     *             @OA\Property(property="status_code", type="integer", example=200),
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 allOf={
     *                     @OA\Schema(ref="#/components/schemas/Rating"),
     *                     @OA\Schema(
     *                         @OA\Property(
     *                             property="media",
     *                             type="array",
     *                             @OA\Items(ref="#/components/schemas/Media")
     *                         )
     *                     )
     *                 }
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Không có quyền cập nhật",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Bạn không có quyền cập nhật trạng thái đánh giá"),
     *             @OA\Property(property="status_code", type="integer", example=403),
     *             @OA\Property(property="success", type="boolean", example=false)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Không tìm thấy đánh giá",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Not Found"),
     *             @OA\Property(property="status_code", type="integer", example=404),
     *             @OA\Property(property="success", type="boolean", example=false)
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Lỗi server",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Internal Server Error"),
     *             @OA\Property(property="status_code", type="integer", example=500),
     *             @OA\Property(property="success", type="boolean", example=false)
     *         )
     *     )
     * )
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected'
        ]);

        $rating = Rating::findOrFail($id);

        if (Auth::user()->role !== 'admin') {
            return $this->respondWithJson(null, 'Bạn không có quyền cập nhật trạng thái đánh giá', 403);
        }

        try {
            if ($request->status === 'approved') {
                $this->ratingService->approveRating($rating);
            } else {
                $this->ratingService->rejectRating($rating);
            }
            
            return $this->respondWithJson($rating->fresh(), 'Cập nhật trạng thái thành công');
        } catch (\Exception $e) {
            return $this->respondWithJson(null, $e->getMessage(), 500);
        }
    }
}
