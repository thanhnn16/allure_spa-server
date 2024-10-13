<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\User;
use App\Models\Treatment;
use App\Models\UserTreatmentPackage;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AppointmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $appointments = Appointment::with(['user', 'treatment', 'staff'])->get();
        
        // Log để kiểm tra dữ liệu
        Log::info('Appointments in controller:', $appointments->toArray());

        return Inertia::render('Calendar/CalendarView', [
            'appointments' => $appointments
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'staff_id' => 'required|exists:users,id',
            'treatment_id' => 'required_without:user_treatment_package_id|exists:treatments,id',
            'user_treatment_package_id' => 'nullable|exists:user_treatment_packages,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'status' => 'required|in:pending,confirmed,cancelled,completed',
            'note' => 'nullable|string',
            'appointment_type' => 'required|in:facial,massage,weight_loss,hair_removal,consultation,others',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            $appointment = Appointment::create([
                'user_id' => $request->user_id,
                'staff_user_id' => $request->staff_id,
                'treatment_id' => $request->treatment_id,
                'start_time' => Carbon::parse($request->start_date),
                'end_time' => Carbon::parse($request->end_date),
                'status' => $request->status,
                'note' => $request->note,
                'appointment_type' => $request->appointment_type,
            ]);

            if ($request->user_treatment_package_id) {
                $userTreatmentPackage = UserTreatmentPackage::findOrFail($request->user_treatment_package_id);
                $userTreatmentPackage->remaining_sessions -= 1;
                $userTreatmentPackage->save();

                $appointment->userTreatmentPackage()->associate($userTreatmentPackage);
                $appointment->save();
            }

            return redirect()->route('appointments.index')->with('success', 'Appointment created successfully.');
        } catch (\Exception $e) {
            Log::error('Error creating appointment: ' . $e->getMessage());
            return back()->withErrors(['error' => 'An error occurred while creating the appointment: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'staff_id' => 'sometimes|exists:users,id',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'status' => 'sometimes|in:pending,confirmed,cancelled,completed',
            'note' => 'sometimes|nullable|string',
            'appointment_type' => 'sometimes|in:facial,massage,weight_loss,hair_removal,consultation,others',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $appointment = Appointment::findOrFail($id);
            
            // Update only the provided fields
            $appointment->fill($request->only([
                'staff_id',
                'start_time',
                'end_time',
                'status',
                'note',
                'appointment_type'
            ]));
            
            $appointment->save();

            // Load related models if they exist
            $appointment->load(['user', 'staff', 'treatment']);

            return response()->json(['message' => 'Appointment updated successfully', 'appointment' => $appointment]);
        } catch (\Exception $e) {
            Log::error('Error updating appointment: ' . $e->getMessage());
            return response()->json(['error' => 'An error occurred while updating the appointment'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
