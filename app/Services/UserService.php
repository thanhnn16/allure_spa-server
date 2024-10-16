<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserTreatmentPackage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;

class UserService
{
    public function getFilteredUsers(Request $request): Builder
    {
        $query = User::where('role', 'user');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('full_name', 'like', '%' . $request->search . '%')
                  ->orWhere('phone_number', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('gender')) {
            $query->where('gender', $request->gender);
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

        return $query;
    }

    public function getUpcomingBirthdays(): int
    {
        return User::where('role', 'user')
            ->whereRaw('DATE_ADD(date_of_birth, 
                INTERVAL YEAR(CURDATE())-YEAR(date_of_birth) 
                         + IF(DAYOFYEAR(CURDATE()) > DAYOFYEAR(date_of_birth),1,0)
                YEAR) 
                BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 15 DAY)')
            ->count();
    }

    public function getUserDetails(string $id): User
    {
        return User::with([
            'addresses',
            'userTreatmentPackages.treatment_combo.treatment',
            'invoices',
            'vouchers'
        ])->findOrFail($id);
    }

    public function searchUsers(string $query, int $limit = 10): array
    {
        try {
            $users = User::where('role', 'user')
                ->where(function ($q) use ($query) {
                    $q->where('full_name', 'like', "%{$query}%")
                        ->orWhere('phone_number', 'like', "%{$query}%");
                })
                ->limit($limit)
                ->get(['id', 'full_name', 'phone_number']);

            return [
                'data' => $users->toArray(),
                'message' => 'Users retrieved successfully',
                'status_code' => 200
            ];
        } catch (\Exception $e) {
            Log::error('Error searching users: ' . $e->getMessage());
            return [
                'data' => [],
                'message' => 'Error searching users',
                'status_code' => 500
            ];
        }
    }

    public function getStaffList(): array
    {
        try {
            $staff = User::where('role', 'staff')->get(['id', 'full_name']);
            return [
                'data' => $staff->toArray(),
                'message' => 'Staff list retrieved successfully',
                'status_code' => 200
            ];
        } catch (\Exception $e) {
            Log::error('Error fetching staff list: ' . $e->getMessage());
            return [
                'data' => [],
                'message' => 'Error fetching staff list',
                'status_code' => 500
            ];
        }
    }

    public function getUserTreatmentPackages(string $userId): array
    {
        return UserTreatmentPackage::where('user_id', $userId)
            ->where('remaining_sessions', '>', 0)
            ->with('treatment') // Eager load the treatment relationship
            ->get()
            ->map(function ($package) {
                return [
                    'id' => $package->id,
                    'treatment' => $package->treatment ? [
                        'id' => $package->treatment->id,
                        'name' => $package->treatment->name,
                    ] : null,
                    'remaining_sessions' => $package->remaining_sessions,
                ];
            })
            ->toArray();
    }

    public function createUser(array $data): User
    {
        try {
            $user = User::create([
                'full_name' => $data['full_name'],
                'phone_number' => $data['phone_number'] ?? null,
                'email' => $data['email'] ?? null,
                'gender' => $data['gender'] ?? 'other',
                'date_of_birth' => $data['date_of_birth'] ?? null,
                'password' => $data['password'],
                'role' => 'user',
            ]);

            return $user;
        } catch (\Exception $e) {
            Log::error('Lỗi khi tạo khách hàng: ' . $e->getMessage());
            throw $e;
        }
    }
}
