<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::where('role', 'user');

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('phone_number', 'like', "%{$search}%");
            });
        }

        if ($request->has('upcoming_birthdays') && $request->input('upcoming_birthdays')) {
            $query->whereRaw('DATE_ADD(date_of_birth, 
                INTERVAL YEAR(CURDATE())-YEAR(date_of_birth) 
                         + IF(DAYOFYEAR(CURDATE()) > DAYOFYEAR(date_of_birth),1,0)
                YEAR) 
                BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 15 DAY)');
        }

        if ($request->has('show_deleted') && $request->input('show_deleted')) {
            $query->withTrashed();
        }

        // Add more filters as needed

        $users = $query->paginate($request->input('per_page', 10));

        Log::info('User query:', ['sql' => $query->toSql(), 'bindings' => $query->getBindings()]);
        Log::info('Users result:', ['count' => $users->count(), 'total' => $users->total()]);

        return Inertia::render('Customers/CustomersView', [
            'users' => $users,
            'filters' => $request->all(),
            'upcomingBirthDays' => User::where('role', 'user')->whereRaw('DATE_ADD(date_of_birth, 
                INTERVAL YEAR(CURDATE())-YEAR(date_of_birth) 
                         + IF(DAYOFYEAR(CURDATE()) > DAYOFYEAR(date_of_birth),1,0)
                YEAR) 
                BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 15 DAY)')->count(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'phone_number' => 'required|string|unique:users,phone_number',
            'email' => 'nullable|email|unique:users,email',
            'gender' => 'required|in:male,female,other',
            'date_of_birth' => 'nullable|date',
            'password' => 'required|string|min:6',
        ]);

        $user = User::create([
            ...$validated,
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()->back()->with('success', 'User created successfully.');
    }

    public function show($id)
    {
        $user = User::with(['addresses', 'treatmentPackages', 'invoices', 'vouchers'])->findOrFail($id);

        return Inertia::render('Customers/CustomerDetails', [
            'user' => $user,
        ]);
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'phone_number' => 'required|string|unique:users,phone_number,' . $id,
            'email' => 'nullable|email|unique:users,email,' . $id,
            'gender' => 'required|in:male,female,other',
            'date_of_birth' => 'nullable|date',
            'note' => 'nullable|string',
        ]);

        $user->update($validated);

        return redirect()->back()->with('success', 'User updated successfully.');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return redirect()->route('users.index')->with('success', 'User deleted successfully.');
    }

    public function searchUsers(Request $request)
    {
        try {
            $query = $request->input('query');
            $users = User::where('role', 'user')
                         ->where(function ($q) use ($query) {
                             $q->where('full_name', 'like', "%{$query}%")
                               ->orWhere('email', 'like', "%{$query}%")
                               ->orWhere('phone_number', 'like', "%{$query}%");
                         })
                         ->get();
            
            return response()->json($users);
        } catch (\Exception $e) {
            Log::error('User search error: ' . $e->getMessage());
            return response()->json(['error' => 'An error occurred while searching users'], 500);
        }
    }
}
