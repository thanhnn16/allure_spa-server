<?php

namespace App\Http\Controllers;

use App\Http\Requests\RecordTreatmentSessionRequest;
use App\Services\ServiceUsageHistoryService;
use Illuminate\Support\Facades\Log;

class ServiceUsageHistoryController extends BaseController
{
    protected $serviceUsageHistoryService;

    public function __construct(ServiceUsageHistoryService $serviceUsageHistoryService)
    {
        $this->serviceUsageHistoryService = $serviceUsageHistoryService;
    }

    /**
     * Record a new treatment session
     * 
     * @OA\Post(
     *     path="/api/treatment-sessions",
     *     summary="Ghi nhận buổi điều trị",
     *     tags={"Treatment"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"user_service_package_id","start_time","staff_user_id"},
     *             @OA\Property(property="user_service_package_id", type="integer"),
     *             @OA\Property(property="start_time", type="string", format="date-time"),
     *             @OA\Property(property="end_time", type="string", format="date-time"),
     *             @OA\Property(property="staff_user_id", type="string"),
     *             @OA\Property(property="result", type="string"),
     *             @OA\Property(property="notes", type="string")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Thành công"),
     *     @OA\Response(response=422, description="Dữ liệu không hợp lệ"),
     *     @OA\Response(response=500, description="Lỗi server")
     * )
     */
    public function store(RecordTreatmentSessionRequest $request)
    {
        try {
            $history = $this->serviceUsageHistoryService->recordSession($request->validated());
            return $this->respondWithJson($history, 'Đã ghi nhận buổi điều trị thành công', 201);
        } catch (\Exception $e) {
            Log::error('Error recording treatment session: ' . $e->getMessage());
            return $this->respondWithError('Có lỗi xảy ra khi ghi nhận buổi điều trị', 500);
        }
    }
} 