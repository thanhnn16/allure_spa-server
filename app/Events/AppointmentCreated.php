<?php

namespace App\Events;

use App\Models\Appointment;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AppointmentCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $appointment;

    public function __construct(Appointment $appointment)
    {
        $this->appointment = $appointment;
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('appointments'),
            new PrivateChannel('appointments.' . $this->appointment->staff_user_id),
        ];
    }

    public function broadcastAs()
    {
        return 'appointment.created';
    }

    public function broadcastWith()
    {
        return [
            'appointment' => $this->appointment->load(['user', 'service', 'timeSlot']),
        ];
    }
} 