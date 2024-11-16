<?php

namespace App\Services;

use App\Models\UserServicePackage;
use App\Models\ServiceUsageHistory;
use Illuminate\Support\Facades\DB;

class ServiceUsageService
{
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