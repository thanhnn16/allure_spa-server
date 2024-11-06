<?php

namespace App\Services;

use App\Services\SearchService;
use App\Services\AppointmentService;
use App\Http\Controllers\TimeSlotController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AiFunctionCallingService
{
    protected $searchService;
    protected $appointmentService;
    protected $timeSlotController;

    public function __construct(
        SearchService $searchService,
        AppointmentService $appointmentService,
        TimeSlotController $timeSlotController
    ) {
        $this->searchService = $searchService;
        $this->appointmentService = $appointmentService;
        $this->timeSlotController = $timeSlotController;
    }

    public function handleFunctionCall($functionName, $args)
    {
        try {
            switch ($functionName) {
                case 'search':
                    return $this->handleSearch($args);
                case 'getAvailableTimeSlots':
                    return $this->handleGetTimeSlots($args);
                case 'createAppointment':
                    return $this->handleCreateAppointment($args);
                default:
                    throw new \Exception("Unknown function: {$functionName}");
            }
        } catch (\Exception $e) {
            Log::error("Function calling error: {$e->getMessage()}");
            throw $e;
        }
    }

    protected function handleSearch($args)
    {
        $query = $args['query'] ?? '';
        $type = $args['type'] ?? 'all';
        $limit = $args['limit'] ?? 10;

        return $this->searchService->search($query, $type, $limit);
    }

    protected function handleGetTimeSlots($args)
    {
        $request = new \Illuminate\Http\Request();
        $request->merge(['date' => $args['date']]);
        
        return $this->timeSlotController->getAvailableSlots($request);
    }

    protected function handleCreateAppointment($args)
    {
        // Add current user ID from context
        $args['user_id'] = Auth::user()->id;
        
        return $this->appointmentService->createAppointment($args);
    }
} 