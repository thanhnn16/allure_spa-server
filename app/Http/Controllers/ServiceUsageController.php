<?php

namespace App\Http\Controllers;

use App\Models\UserServicePackage;
use App\Services\ServiceUsageService;
use Illuminate\Http\Request;

class ServiceUsageController extends BaseController
{
    protected $serviceUsageService;

    public function __construct(ServiceUsageService $serviceUsageService)
    {
        $this->serviceUsageService = $serviceUsageService;
    }

    public function recordUsage(Request $request, UserServicePackage $package)
    {
        try {
            $validated = $request->validate([
                'staff_user_id' => 'required|exists:users,id',
                'result' => 'nullable|string',
                'notes' => 'nullable|string'
            ]);

            $usage = $this->serviceUsageService->recordUsage(
                $package,
                $validated['staff_user_id'],
                $validated['result'] ?? null,
                $validated['notes'] ?? null
            );

            return $this->respondWithJson($usage, 'Đã ghi nhận buổi điều trị thành công');
        } catch (\Exception $e) {
            return $this->respondWithError($e->getMessage());
        }
    }

    public function getUsageHistory(UserServicePackage $package)
    {
        $history = $this->serviceUsageService->getPackageUsageHistory($package);
        return $this->respondWithJson($history, 'Lấy lịch sử điều trị thành công');
    }
} 