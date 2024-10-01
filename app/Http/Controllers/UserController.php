<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $perPage = $request->input('per_page', 10);
        $showUpcomingBirthdays = $request->input('upcoming_birthdays', false);
        $showDeletedUsers = $request->input('show_deleted', false);
        $gender = $request->input('gender');
        $ageRange = $request->input('age_range');
        $pointRange = $request->input('point_range');
        $purchaseCountRange = $request->input('purchase_count_range');
        $createdAtRange = $request->input('created_at_range');

        $query = User::where('role', 'user');

        if ($showDeletedUsers) {
            $query->withTrashed();
        } else {
            $query->whereNull('deleted_at');
        }

        if ($showUpcomingBirthdays) {
            $query->whereRaw('DATE_ADD(date_of_birth, 
                INTERVAL YEAR(CURDATE())-YEAR(date_of_birth) 
                    + IF(DAYOFYEAR(CURDATE()) > DAYOFYEAR(date_of_birth), 1, 0) YEAR) 
                BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 15 DAY)');
        } else {
            $query->when($search, function ($q) use ($search) {
                $q->where(function ($subq) use ($search) {
                    $subq->where('full_name', 'like', "%{$search}%")
                        ->orWhere('phone_number', 'like', "%{$search}%");
                });
            });
        }

        if ($gender) {
            $query->where('gender', $gender);
        }

        if ($ageRange) {
            list($minAge, $maxAge) = explode('-', $ageRange);
            $query->whereRaw('TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) BETWEEN ? AND ?', [$minAge, $maxAge]);
        }

        if ($pointRange) {
            list($minPoint, $maxPoint) = explode('-', $pointRange);
            $query->whereBetween('point', [$minPoint, $maxPoint]);
        }

        if ($purchaseCountRange) {
            list($minPurchase, $maxPurchase) = explode('-', $purchaseCountRange);
            $query->whereBetween('purchase_count', [$minPurchase, $maxPurchase]);
        }

        if ($createdAtRange) {
            list($startDate, $endDate) = explode(' - ', $createdAtRange);
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        $users = $query->paginate($perPage);

        $upcomingBirthdays = User::whereRaw('DATE_ADD(date_of_birth, 
            INTERVAL YEAR(CURDATE())-YEAR(date_of_birth) 
                + IF(DAYOFYEAR(CURDATE()) > DAYOFYEAR(date_of_birth), 1, 0) YEAR) 
            BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 15 DAY)')
            ->count();

        return Inertia::render('Customers/CustomersView', [
            'users' => $users,
            'filters' => $request->all(),
            'upcomingBirthdays' => $upcomingBirthdays
        ]);
    }

    public function profile()
    {
        return Inertia::render('Customers/ProfileView');
    }

    public function updateProfile(Request $request)
    {
        $user = User::find($request->id);
        $user->update($request->all());
        return redirect()->route('users.profile')->with('success', 'Profile updated successfully');
    }

    /**
     * Store a newly created resource in storage.
     */
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

        $user = User::create($validated);

        return redirect()->route('users.index')->with('success', 'Khách hàng đã được thêm thành công.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $user = User::withTrashed()->with([
            'addresses',
            'treatmentPackages.treatmentCombo.treatment',
            'vouchers',
            'invoices'  // Đảm bảo rằng tên này khớp với tên mối quan hệ trong model User
        ])->findOrFail($id);

        $upcomingBirthdays = User::whereMonth('date_of_birth', '=', now()->addDays(15)->month)
            ->whereDay('date_of_birth', '>=', now()->day)
            ->whereDay('date_of_birth', '<=', now()->addDays(15)->day)
            ->count();

        return Inertia::render('Customers/CustomerDetails', [
            'user' => $user,
            'upcomingBirthdays' => $upcomingBirthdays
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        try {
            $validated = $request->validate([
                'full_name' => 'required|string|max:255',
                'phone_number' => 'required|string|unique:users,phone_number,' . $user->id,
                'email' => 'nullable|email|unique:users,email,' . $user->id,
                'gender' => 'required|in:male,female,other',
                'date_of_birth' => 'nullable|date',
                'note' => 'nullable|string',
            ]);

            $user->update($validated);

            return response()->json([
                'message' => 'Thông tin khách hàng đã được cập nhật thành công.',
                'user' => $user
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Dữ liệu không hợp lệ.',
                'errors' => $e->errors()
            ], 422);
        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Lỗi cơ sở dữ liệu khi cập nhật thông tin khách hàng.',
                'error' => $e->getMessage()
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Có lỗi xảy ra khi cập nhật thông tin khách hàng.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        try {
            $user->delete(); // This will perform a soft delete
            return response()->json([
                'message' => 'Khách hàng đã được xóa thành công.'
            ]);
        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Lỗi cơ sở dữ liệu khi xóa khách hàng.',
                'error' => $e->getMessage()
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Có lỗi xảy ra khi xóa khách hàng.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function search(Request $request): JsonResponse
    {
        $query = $request->input('query');
        $users = User::where('full_name', 'like', "%{$query}%")
            ->orWhere('phone_number', 'like', "%{$query}%")
            ->limit(10)
            ->get(['id', 'full_name', 'phone_number']);

        return response()->json($users);
    }
}
