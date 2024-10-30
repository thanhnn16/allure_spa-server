<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserServicePackage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\UsersImport;

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
            'userServicePackages.service_combo.service',
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

    public function getUserServicePackages(string $userId): array
    {
        return UserServicePackage::where('user_id', $userId)
            ->where('remaining_sessions', '>', 0)
            ->with('service') // Eager load the service relationship
            ->get()
            ->map(function ($package) {
                return [
                    'id' => $package->id,
                    'service' => $package->service ? [
                        'id' => $package->service->id,
                        'name' => $package->service->name,
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

    public function importUsers($file)
    {
        DB::beginTransaction();
        try {
            Log::info('Starting import process');
            $import = new UsersImport();
            Excel::import($import, $file);

            $results = [
                'total' => $import->getRowCount(),
                'successful' => $import->getSuccessCount(),
                'failed' => $import->getFailureCount(),
                'failures' => $import->failures(),
            ];

            Log::info('Import completed', $results);

            DB::commit();
            return $results;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error during import: ' . $e->getMessage());
            throw $e;
        }
    }

    public function updateUser(string $id, array $data): User
    {
        $user = User::findOrFail($id);
        $user->update($data);
        return $user->fresh();
    }
}
