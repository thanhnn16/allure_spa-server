<?php

namespace App\Http\Controllers;

use App\Services\FavoriteService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FavoriteController extends BaseController
{
    protected $favoriteService;

    public function __construct(FavoriteService $favoriteService)
    {
        $this->favoriteService = $favoriteService;
    }

    /**
     * @OA\Post(
     *     path="/api/favorites/toggle",
     *     summary="Toggle favorite status for product or service",
     *     tags={"Favorites"},
     *     security={{ "bearerAuth": {} }},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"type", "item_id"},
     *             @OA\Property(property="type", type="string", enum={"product", "service"}, description="Type of item to favorite"),
     *             @OA\Property(property="item_id", type="integer", description="ID of product or service")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="status_code", type="integer", example=200),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="status", type="string", enum={"added", "removed"}),
     *                 @OA\Property(property="message", type="string")
     *             )
     *         )
     *     )
     * )
     */
    public function toggle(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:product,service',
            'item_id' => 'required|integer'
        ]);

        if ($validator->fails()) {
            return $this->respondWithError(422, $validator->errors()->first());
        }

        $result = $this->favoriteService->toggleFavorite(
            $request->type,
            $request->item_id
        );

        return $this->respondWithJson($result);
    }

    /**
     * @OA\Get(
     *     path="/api/favorites",
     *     summary="Get all user's favorites",
     *     tags={"Favorites"},
     *     security={{ "bearerAuth": {} }},
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="status_code", type="integer", example=200),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/responses/FavoriteResponse")
     *             )
     *         )
     *     )
     * )
     */
    public function index()
    {
        $favorites = $this->favoriteService->getUserFavorites();
        return $this->respondWithJson($favorites);
    }

    /**
     * @OA\Get(
     *     path="/api/favorites/{type}",
     *     summary="Get user's favorites by type",
     *     tags={"Favorites"},
     *     security={{ "bearerAuth": {} }},
     *     @OA\Parameter(
     *         name="type",
     *         in="path",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *             enum={"product", "service"}
     *         ),
     *         description="Type of favorite items to retrieve"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example=""),
     *             @OA\Property(property="status_code", type="integer", example=200),
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=62),
     *                     @OA\Property(property="favorite_type", type="string", enum={"product", "service"}, example="service"),
     *                     @OA\Property(property="item_id", type="integer", example=1),
     *                     @OA\Property(property="user_id", type="string", example="03180146-1b90-42e0-b5d2-8519c20e647d"),
     *                     @OA\Property(property="created_at", type="string", format="date-time"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time"),
     *                     @OA\Property(
     *                         property="service",
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="service_name", type="string", example="Chăm da Amino - Phù hợp mọi loại da"),
     *                         @OA\Property(property="description", type="string", example="Liệu trình chăm sóc da mặt phù hợp cho mọi loại da"),
     *                         @OA\Property(property="duration", type="integer", example=60),
     *                         @OA\Property(property="category_id", type="integer", example=1),
     *                         @OA\Property(property="single_price", type="integer", example=1350000),
     *                         @OA\Property(property="combo_5_price", type="integer", example=5400000),
     *                         @OA\Property(property="combo_10_price", type="integer", example=9450000),
     *                         @OA\Property(property="validity_period", type="integer", example=365),
     *                         @OA\Property(property="created_at", type="string", format="date-time", nullable=true),
     *                         @OA\Property(property="updated_at", type="string", format="date-time", nullable=true),
     *                         @OA\Property(property="deleted_at", type="string", format="date-time", nullable=true)
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="status_code", type="integer", example=422)
     *         )
     *     )
     * )
     */
    public function getByType(string $type)
    {
        $validator = Validator::make(['type' => $type], [
            'type' => 'required|in:product,service'
        ]);

        if ($validator->fails()) {
            return $this->respondWithError(422, $validator->errors()->first());
        }

        $favorites = $this->favoriteService->getFavoritesByType($type);
        return $this->respondWithJson($favorites);
    }
}
