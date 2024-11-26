<?php

namespace App\Services;

use App\Models\UserServicePackage;
use App\Models\ServiceUsageHistory;
use Illuminate\Support\Facades\DB;

class ServiceUsageService
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function recordUsage(
        UserServicePackage $package,
        string $staffUserId,
        string $result = null,
        string $notes = null
    ) {
        return DB::transaction(function () use ($package, $staffUserId, $result, $notes) {
            // Check if package has remaining sessions
            if ($package->remaining_sessions <= 0) {
                throw new \Exception('Không còn buổi điều trị trong gói');
            }

            // Create usage history
            $usage = ServiceUsageHistory::create([
                'user_service_package_id' => $package->id,
                'staff_user_id' => $staffUserId,
                'start_time' => now(),
                'end_time' => now()->addMinutes($package->service->duration ?? 60),
                'result' => $result,
                'notes' => $notes
            ]);

            // Update package used sessions
            $package->increment('used_sessions');

            // Kiểm tra và gửi thông báo khi còn ít buổi
            if ($package->remaining_sessions <= 2) {
                $this->notificationService->createNotification([
                    'user_id' => $package->user_id,
                    'type' => NotificationService::NOTIFICATION_TYPES['service']['low_sessions'],
                    'data' => [
                        'service' => $package->service->name,
                        'remaining' => $package->remaining_sessions,
                        'package_id' => $package->id,
                        'service_id' => $package->service_id
                    ]
                ]);
            }

            // Gửi thông báo khi hoàn thành buổi điều trị
            $this->notificationService->createNotification([
                'user_id' => $package->user_id,
                'type' => NotificationService::NOTIFICATION_TYPES['service']['completed'],
                'data' => [
                    'service' => $package->service->name,
                    'remaining' => $package->remaining_sessions,
                    'package_id' => $package->id,
                    'service_id' => $package->service_id,
                    'staff_name' => $usage->staff->full_name
                ]
            ]);

            return $usage;
        });
    }

    public function getPackageUsageHistory(UserServicePackage $package)
    {
        return $package->usageHistories()
            ->with('staff:id,full_name')
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
