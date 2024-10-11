<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AppointmentController extends Controller
{
    public function index()
    {
        $appointments = Appointment::with(['user', 'staff', 'orderItem'])->get();
        
        return Inertia::render('Calendar/CalendarView', [
            'appointments' => $appointments
        ]);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'user_id' => 'required|exists:users,id',
            'appointment_type' => 'required|in:facial,massage,hair_removal,consultation,weight_loss,other',
            'staff_id' => 'nullable|exists:users,id',
            'order_item_id' => 'nullable|exists:order_items,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'is_all_day' => 'boolean',
            'status' => 'required|in:pending,confirmed,cancelled',
            'note' => 'nullable|string',
        ]);

        $appointment = Appointment::create($validatedData);
        
        if ($request->wantsJson()) {
            return response()->json($appointment, 201);
        }
        
        return redirect()->back()->with('success', 'Appointment created successfully.');
    }

    public function update(Request $request, Appointment $appointment)
    {
        $validatedData = $request->validate([
            'user_id' => 'sometimes|required|exists:users,id',
            'appointment_type' => 'sometimes|required|in:facial,massage,hair_removal,consultation,weight_loss,other',
            'staff_id' => 'nullable|exists:users,id',
            'order_item_id' => 'nullable|exists:order_items,id',
            'start_date' => 'sometimes|required|date',
            'end_date' => 'sometimes|required|date|after:start_date',
            'is_all_day' => 'boolean',
            'status' => 'sometimes|required|in:pending,confirmed,cancelled',
            'note' => 'nullable|string',
        ]);

        $appointment->update($validatedData);
        
        if ($request->wantsJson()) {
            return response()->json($appointment);
        }
        
        return redirect()->back()->with('success', 'Appointment updated successfully.');
    }

    public function destroy(Request $request, Appointment $appointment)
    {
        $appointment->delete();
        
        if ($request->wantsJson()) {
            return response()->json(null, 204);
        }
        
        return redirect()->back()->with('success', 'Appointment deleted successfully.');
    }
}
