<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserTreatmentPackage;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function searchUsers(Request $request)
    {
        $query = $request->get('query');
        $users = User::where('role', 'user')
            ->where(function ($q) use ($query) {
                $q->where('full_name', 'like', "%{$query}%")
                    ->orWhere('phone_number', 'like', "%{$query}%");
            })
            ->limit(10)
            ->get(['id', 'full_name', 'phone_number']);

        return response()->json($users);
    }

    public function getStaffList()
    {
        $staff = User::where('role', 'staff')->get(['id', 'full_name']);
        return response()->json(['staff' => $staff]);
    }

    public function getUserTreatmentPackages($userId)
    {
        $userTreatmentPackages = UserTreatmentPackage::where('user_id', $userId)
            ->where('remaining_sessions', '>', 0)
            ->with('treatment')
            ->get();

        return response()->json($userTreatmentPackages);
    }
}
