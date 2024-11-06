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
     *             @OA\Property(property="type", type="string", enum={"product", "service"}),
     *             @OA\Property(property="item_id", type="integer")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="status_code", type="integer"),
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="status", type="string"),
     *                 @OA\Property(property="message", type="string")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
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
     *     summary="Get user's favorites",
     *     tags={"Favorites"},
     *     security={{ "bearerAuth": {} }},
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="status_code", type="integer"),
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/Favorite")
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
     *     summary="Get user's favorites by type (products or services)",
     *     tags={"Favorites"},
     *     security={{ "bearerAuth": {} }},
     *     @OA\Parameter(
     *         name="type",
     *         in="path",
     *         required=true,
     *         description="Type of favorites to retrieve",
     *         @OA\Schema(
     *             type="string",
     *             enum={"product", "service"}
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="status_code", type="integer"),
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="user_id", type="integer"),
     *                     @OA\Property(property="product_id", type="integer", nullable=true),
     *                     @OA\Property(property="service_id", type="integer", nullable=true),
     *                     @OA\Property(
     *                         property="product",
     *                         nullable=true,
     *                         ref="#/components/schemas/Product"
     *                     ),
     *                     @OA\Property(
     *                         property="service",
     *                         nullable=true,
     *                         ref="#/components/schemas/Service"
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function getByType($type)
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