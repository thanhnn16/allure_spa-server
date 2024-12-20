<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class StaffController extends BaseController
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function index(Request $request)
    {
        $staff = $this->userService->getFilteredStaff($request)
            ->paginate($request->input('per_page', 10));

        if ($request->expectsJson()) {
            return $this->respondWithJson($staff, 'Staff list retrieved successfully');
        }

        return $this->respondWithInertia('Staff/StaffView', [
            'staff' => $staff,
            'filters' => $request->all(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'phone_number' => 'nullable|string|max:255|unique:users,phone_number',
            'email' => 'nullable|email|max:255|unique:users,email',
            'gender' => 'nullable|in:male,female,other',
            'date_of_birth' => 'nullable|date',
            'staff_detail' => 'nullable|array',
            'staff_detail.position' => 'nullable|string|max:255',
            'staff_detail.department' => 'nullable|string|max:255',
            'staff_detail.hire_date' => 'nullable|date',
        ]);

        $validated['password'] = Hash::make('allurespa');

        try {
            $staff = $this->userService->createStaff($validated);

            if ($request->expectsJson()) {
                return $this->respondWithJson($staff, 'Đã thêm nhân viên thành công', 201);
            }

            return redirect()->route('staff.index')->with('success', 'Đã thêm nhân viên thành công');
        } catch (\Exception $e) {
            Log::error('Error creating staff: ' . $e->getMessage());
            return $this->respondWithError('Có lỗi xảy ra khi tạo nhân viên', 500);
        }
    }

    public function show(string $id)
    {
        $staff = $this->userService->getUserDetails($id);

        if (request()->expectsJson()) {
            return $this->respondWithJson($staff, 'Staff details retrieved successfully');
        }

        return $this->respondWithInertia('Staff/StaffDetails', [
            'staff' => $staff,
        ]);
    }

    public function update(Request $request, string $id)
    {
        try {
            $staff = $this->userService->updateUser($id, $request->validated());

            if ($request->expectsJson()) {
                return $this->respondWithJson($staff, 'Thông tin nhân viên đã được cập nhật thành công');
            }

            return redirect()->back()->with('success', 'Thông tin nhân viên đã được cập nhật thành công');
        } catch (\Exception $e) {
            Log::error('Error updating staff: ' . $e->getMessage());
            return $this->respondWithError('Có lỗi xảy ra khi cập nhật thông tin nhân viên', 500);
        }
    }

    public function destroy(string $id)
    {
        try {
            $staff = User::findOrFail($id);
            $staff->delete();

            if (request()->expectsJson()) {
                return $this->respondWithJson(null, 'Đã xóa nhân viên thành công');
            }

            return redirect()->route('staff.index')
                ->with('success', 'Đã xóa nhân viên thành công');
        } catch (\Exception $e) {
            Log::error('Error deleting staff: ' . $e->getMessage());
            return $this->respondWithError('Có lỗi xảy ra khi xóa nhân viên', 500);
        }
    }
} 