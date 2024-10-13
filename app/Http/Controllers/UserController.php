<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Inertia\Inertia;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = User::where('role', 'user');

        // Apply filters
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('full_name', 'like', '%' . $request->search . '%')
                  ->orWhere('phone_number', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('gender')) {
            $query->where('gender', $request->gender);
        }

        if ($request->filled('age_range')) {
            // Implement age range filter
        }

        if ($request->filled('loyalty_points_range')) {
            // Implement loyalty points range filter
        }

        if ($request->filled('purchase_count_range')) {
            // Implement purchase count range filter
        }

        if ($request->filled('created_at_range')) {
            // Implement created at range filter
        }

        if ($request->boolean('upcoming_birthdays')) {
            $query->whereRaw('DATE_ADD(date_of_birth, 
                INTERVAL YEAR(CURDATE())-YEAR(date_of_birth) 
                         + IF(DAYOFYEAR(CURDATE()) > DAYOFYEAR(date_of_birth),1,0)
                YEAR) 
                BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 15 DAY)');
        }

        if ($request->boolean('show_deleted')) {
            $query->withTrashed();
        }

        $users = $query->paginate($request->input('per_page', 10));

        $upcomingBirthdays = User::where('role', 'user')
            ->whereRaw('DATE_ADD(date_of_birth, 
                INTERVAL YEAR(CURDATE())-YEAR(date_of_birth) 
                         + IF(DAYOFYEAR(CURDATE()) > DAYOFYEAR(date_of_birth),1,0)
                YEAR) 
                BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 15 DAY)')
            ->count();

        return Inertia::render('Customers/CustomersView', [
            'users' => $users,
            'filters' => $request->all(),
            'upcomingBirthdays' => $upcomingBirthdays,
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
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user = User::with([
            'addresses',
            'userTreatmentPackages.treatment_combo.treatment',
            'invoices',
            'vouchers'
        ])->findOrFail($id);

        $upcomingBirthdays = User::where('role', 'user')
            ->whereRaw('DATE_ADD(date_of_birth, 
                INTERVAL YEAR(CURDATE())-YEAR(date_of_birth) 
                         + IF(DAYOFYEAR(CURDATE()) > DAYOFYEAR(date_of_birth),1,0)
                YEAR) 
                BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 15 DAY)')
            ->count();

        return Inertia::render('Customers/CustomerDetails', [
            'user' => $user,
            'upcomingBirthdays' => $upcomingBirthdays,
        ]);
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
}
