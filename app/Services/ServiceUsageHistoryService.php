<?php

namespace App\Services;

use App\Models\ServiceUsageHistory;
use App\Models\UserServicePackage;
use App\Models\Appointment;
use Illuminate\Support\Facades\DB;

class ServiceUsageHistoryService
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Record a new treatment session
     */
    public function recordSession(array $data): ServiceUsageHistory
    {
        DB::beginTransaction();
        try {
            // Kiểm tra gói dịch vụ còn hiệu lực
            $package = UserServicePackage::findOrFail($data['user_service_package_id']);

            // Kiểm tra tính hợp lệ của gói dịch vụ
            if ($package->status === 'expired') {
                throw new \Exception('Gói dịch vụ đã hết hạn');
            }

            if ($package->remaining_sessions <= 0) {
                throw new \Exception('Gói dịch vụ đã hết số buổi sử dụng');
            }

            // Kiểm tra xem có appointment_id không
            if (!empty($data['appointment_id'])) {
                $appointment = Appointment::find($data['appointment_id']);
                if ($appointment && $appointment->status !== 'completed') {
                    throw new \Exception('Chỉ có thể ghi nhận sử dụng dịch vụ cho lịch hẹn đã hoàn thành');
                }
            }

            // Ghi nhận lịch sử điều trị
            $history = ServiceUsageHistory::create([
                'user_service_package_id' => $data['user_service_package_id'],
                'start_time' => $data['start_time'],
                'end_time' => $data['end_time'] ?? null,
                'staff_user_id' => $data['staff_user_id'],
                'result' => $data['result'] ?? null,
                'notes' => $data['notes'] ?? null,
                'appointment_id' => $data['appointment_id'] ?? null
            ]);

            // Cập nhật số buổi đã sử dụng (luôn trừ 1 bất kể số slot của lịch hẹn)
            $package->used_sessions += 1;
            $package->save();

            // Kiểm tra và thông báo khi còn ít buổi
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
        if ($package->remaining_sessions === 2) {
            $this->notificationService->createNotification([
                'user_id' => $package->user_id,
                'title' => [
                    'en' => 'Service Package Running Low',
                    'vi' => 'Gói dịch vụ sắp hết hạn',
                    'ja' => 'サービスパッケージの残りが少なくなっています'
                ],
                'content' => [
                    'en' => "Your {$package->service->name} package has only 2 sessions remaining",
                    'vi' => "Gói dịch vụ {$package->service->name} của bạn còn 2 buổi cuối",
                    'ja' => "{$package->service->name}パッケージの残りセッションが2回となっています"
                ],
                'type' => NotificationService::NOTIFICATION_TYPES['service']['low_sessions'],
                'data' => [
                    'package_id' => $package->id,
                    'service_id' => $package->service_id,
                    'remaining_sessions' => $package->remaining_sessions
                ]
            ]);
        }
    }
}
