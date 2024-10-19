<?php

namespace App\Http\Controllers;

use App\Services\RatingService;
use Illuminate\Http\Request;
use App\Http\Requests\CreateRatingRequest;
use OpenApi\Annotations as OA;

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
        return $this->respondWithJson($ratings, 'Danh sách đánh giá');
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
     *     path="/api/treatments/{treatmentId}/ratings",
     *     summary="Lấy danh sách đánh giá cho một liệu trình",
     *     description="Trả về danh sách đánh giá cho một liệu trình cụ thể với phân trang",
     *     operationId="getTreatmentRatings",
     *     tags={"Ratings"},
     *     @OA\Parameter(
     *         name="treatmentId",
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
     *                 @OA\Property(property="first_page_url", type="string", example="http://localhost/api/treatments/1/ratings?page=1"),
     *                 @OA\Property(property="from", type="integer", example=1),
     *                 @OA\Property(property="last_page", type="integer", example=1),
     *                 @OA\Property(property="last_page_url", type="string", example="http://localhost/api/treatments/1/ratings?page=1"),
     *                 @OA\Property(property="next_page_url", type="string", example=null),
     *                 @OA\Property(property="path", type="string", example="http://localhost/api/treatments/1/ratings"),
     *                 @OA\Property(property="per_page", type="integer", example=15),
     *                 @OA\Property(property="prev_page_url", type="string", example=null),
     *                 @OA\Property(property="to", type="integer", example=15),
     *                 @OA\Property(property="total", type="integer", example=15)
     *             )
     *         )
     *     )
     * )
     */
    public function getTreatmentRatings(Request $request, $treatmentId)
    {
        $ratings = $this->ratingService->getRatingsByTreatment($treatmentId, $request->all());
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
     *             @OA\Property(property="rating_type", type="string", enum={"treatment", "product"}, example="product"),
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
}
