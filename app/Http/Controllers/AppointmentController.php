<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AppointmentController extends Controller
{
    public function index()
    {
        return Appointment::all();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'appointment_type_id' => 'required|exists:appointment_types,id',
            'staff_id' => 'nullable|exists:staffs,id',
            'order_item_id' => 'required|exists:order_items,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'is_all_day' => 'boolean',
            'status' => 'required|in:pending,confirmed,cancelled',
            'note' => 'nullable|string',
        ]);

        $appointment = Appointment::create($validated);

        return response()->json($appointment, 201);
    }

    public function update(Request $request, Appointment $appointment)
    {
        $validated = $request->validate([
            'user_id' => 'sometimes|exists:users,id',
            'appointment_type_id' => 'sometimes|exists:appointment_types,id',
            'staff_id' => 'nullable|exists:staffs,id',
            'order_item_id' => 'sometimes|exists:order_items,id',
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date|after:start_date',
            'is_all_day' => 'sometimes|boolean',
            'status' => 'sometimes|in:pending,confirmed,cancelled',
            'note' => 'nullable|string',
        ]);

        $appointment->update($validated);

        return response()->json($appointment);
    }

    public function destroy(Appointment $appointment)
    {
        $appointment->delete();
        return response()->json(null, 204);
    }
}
