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
     *         @OA\Schema(type="string")
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
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="user_id", type="string"),
     *                     @OA\Property(property="favorite_type", type="string", enum={"product", "service"}),
     *                     @OA\Property(property="item_id", type="integer"),
     *                     @OA\Property(property="created_at", type="string", format="date-time"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time"),
     *                     @OA\Property(
     *                         property="item_details",
     *                         type="object",
     *                         oneOf={
     *                             @OA\Schema(ref="#/components/schemas/ProductResponse"),
     *                             @OA\Schema(ref="#/components/schemas/ServiceResponse")
     *                         }
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
