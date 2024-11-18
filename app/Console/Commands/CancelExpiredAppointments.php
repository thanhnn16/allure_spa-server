<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AppointmentService;
use App\Models\Appointment;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class CancelExpiredAppointments extends Command
{
    protected $signature = 'appointments:cancel-expired';
    protected $description = 'Cancel expired appointments that have passed their scheduled date';

    protected $appointmentService;

    public function __construct(AppointmentService $appointmentService)
    {
        parent::__construct();
        $this->appointmentService = $appointmentService;
    }

    public function handle()
    {
        try {
            $this->info('Starting to check for expired appointments...');

            // Get all pending appointments that have passed their scheduled date
            $expiredAppointments = Appointment::with(['timeSlot', 'user'])
                ->where('status', 'pending')
                ->where(function ($query) {
                    $query->whereDate('appointment_date', '<', Carbon::today())
                        ->orWhere(function ($q) {
                            $q->whereDate('appointment_date', '=', Carbon::today())
                                ->whereHas('timeSlot', function ($timeSlotQuery) {
                                    $timeSlotQuery->where('end_time', '<', Carbon::now()->format('H:i:s'));
                                });
                        });
                })
                ->get();

            $count = 0;
            foreach ($expiredAppointments as $appointment) {
                $result = $this->appointmentService->cancelAppointment(
                    $appointment->id,
                    'Tự động hủy do quá thời gian đặt lịch',
                    true // flag để đánh dấu là hủy tự động
                );

                if ($result['success']) {
                    $count++;
                }
            }

            $message = "Đã hủy {$count} lịch hẹn quá hạn";
            $this->info($message);
            Log::info($message);

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $errorMessage = "Lỗi khi hủy lịch hẹn quá hạn: " . $e->getMessage();
            $this->error($errorMessage);
            Log::error($errorMessage);

            return Command::FAILURE;
        }
    }
}
