<?php

namespace App\Http\Controllers;

use App\Models\LoginHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use OpenApi\Annotations as OA;

class LoginHistoryController extends BaseController
{
    /**
     * @OA\Get(
     *     path="/api/auth/login-histories",
     *     summary="Lấy lịch sử đăng nhập của người dùng",
     *     description="Trả về danh sách lịch sử đăng nhập được phân trang của người dùng hiện tại",
     *     operationId="getLoginHistories",
     *     tags={"Authentication"},
     *     security={{ "bearerAuth": {} }},
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Số lượng bản ghi trên mỗi trang",
     *         required=false,
     *         @OA\Schema(type="integer", default=10)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lấy lịch sử đăng nhập thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Lấy lịch sử đăng nhập thành công"),
     *             @OA\Property(property="status_code", type="integer", example=200),
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="data", type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="user_id", type="string", example="c1a04658-eb98-472c-908c-3b1c41a3e982"),
     *                         @OA\Property(property="ip_address", type="string", example="127.0.0.1"),
     *                         @OA\Property(property="user_agent", type="string", example="PostmanRuntime/7.43.0"),
     *                         @OA\Property(property="login_at", type="string", format="date-time"),
     *                         @OA\Property(property="status", type="string", example="success"),
     *                         @OA\Property(property="device_type", type="string", example="desktop"),
     *                         @OA\Property(property="location", type="string", example="Local"),
     *                         @OA\Property(property="created_at", type="string", format="date-time"),
     *                         @OA\Property(property="updated_at", type="string", format="date-time")
     *                     )
     *                 ),
     *                 @OA\Property(property="first_page_url", type="string", example="http://127.0.0.1:8000/api/auth/login-histories?page=1"),
     *                 @OA\Property(property="from", type="integer", example=1),
     *                 @OA\Property(property="last_page", type="integer", example=1),
     *                 @OA\Property(property="last_page_url", type="string", example="http://127.0.0.1:8000/api/auth/login-histories?page=1"),
     *                 @OA\Property(property="links", type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="url", type="string", nullable=true),
     *                         @OA\Property(property="label", type="string", example="1"),
     *                         @OA\Property(property="active", type="boolean", example=true)
     *                     )
     *                 ),
     *                 @OA\Property(property="next_page_url", type="string", nullable=true),
     *                 @OA\Property(property="path", type="string", example="http://127.0.0.1:8000/api/auth/login-histories"),
     *                 @OA\Property(property="per_page", type="integer", example=10),
     *                 @OA\Property(property="prev_page_url", type="string", nullable=true),
     *                 @OA\Property(property="to", type="integer", example=1),
     *                 @OA\Property(property="total", type="integer", example=1)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Chưa xác thực"
     *     )
     * )
     */
    public function index(Request $request)
    {
        $histories = LoginHistory::where('user_id', Auth::user()->id)
            ->orderBy('login_at', 'desc')
            ->paginate($request->per_page ?? 10);

        return $this->respondWithJson($histories, 'Lấy lịch sử đăng nhập thành công');
    }
}
