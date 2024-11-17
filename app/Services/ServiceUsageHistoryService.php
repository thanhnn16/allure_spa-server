<?php

namespace App\Services;

use App\Models\ServiceUsageHistory;
use App\Models\UserServicePackage;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Log;

class ServiceUsageHistoryService
{
    /**
     * Record a new treatment session
     */
    public function recordSession(array $data): ServiceUsageHistory
    {
        DB::beginTransaction();
        try {
            // Kiểm tra gói dịch vụ còn hiệu lực
            $package = UserServicePackage::findOrFail($data['user_service_package_id']);
            if ($package->status === 'expired' || $package->remaining_sessions <= 0) {
                throw new \Exception('Gói dịch vụ đã hết hạn hoặc hết số buổi');
            }

            // Ghi nhận lịch sử điều trị
            $history = ServiceUsageHistory::create([
                'user_service_package_id' => $data['user_service_package_id'],
                'start_time' => $data['start_time'],
                'end_time' => $data['end_time'] ?? null,
                'staff_user_id' => $data['staff_user_id'],
                'result' => $data['result'] ?? null,
                'notes' => $data['notes'] ?? null
            ]);

            // Cập nhật số buổi đã sử dụng
            $package->used_sessions += 1;
            $package->save();

            // Nếu đây là buổi cuối cùng, cập nhật trạng thái gói
            if ($package->remaining_sessions === 0) {
                $package->status = 'completed';
                $package->save();
            }

            // Kiểm tra và thông báo khi còn 2 buổi cuối
            $this->checkAndNotifyLowSessions($package);

            DB::commit();
            return $history;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function checkAndNotifyLowSessions(UserServicePackage $package): void
    {
        // Thông báo khi còn 2 buổi cuối
        if ($package->remaining_sessions === 2) {
            // Gửi thông báo cho khách hàng
            // Có thể thêm notification system riêng
            Log::info("Package {$package->id} for user {$package->user_id} has only 2 sessions remaining");
        }
    }
}
