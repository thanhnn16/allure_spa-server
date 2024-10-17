<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\UserService;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\ImportUsersRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class UserController extends BaseController
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $users = $this->userService->getFilteredUsers($request)->paginate($request->input('per_page', 10));
        $upcomingBirthdays = $this->userService->getUpcomingBirthdays();

        if ($request->expectsJson()) {
            return $this->respondWithJson($users, 'Users retrieved successfully');
        }

        return $this->respondWithInertia('Customers/CustomersView', [
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
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'phone_number' => 'nullable|string|max:255|unique:users,phone_number',
            'email' => 'nullable|email|max:255|unique:users,email',
            'gender' => 'nullable|in:male,female,other',
            'date_of_birth' => 'nullable|date',
        ]);

        $validated['password'] = Hash::make('allurespa');

        $user = $this->userService->createUser($validated);

        if ($request->expectsJson()) {
            return $this->respondWithJson($user, 'Đã thêm khách hàng thành công', 201);
        }

        return redirect()->route('users.index')->with('success', 'Đã thêm khách hàng thành công');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user = $this->userService->getUserDetails($id);
        $upcomingBirthdays = $this->userService->getUpcomingBirthdays();

        if (request()->expectsJson()) {
            return $this->respondWithJson($user, 'User details retrieved successfully');
        }

        return $this->respondWithInertia('Customers/CustomerDetails', [
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

    public function searchUsers(Request $request)
    {
        $query = $request->get('query');
        $limit = $request->get('limit', 10);
        $result = $this->userService->searchUsers($query, $limit);
        return $this->respondWithJson($result['data'], $result['message'], $result['status_code']);
    }

    public function getStaffList()
    {
        $result = $this->userService->getStaffList();
        return $this->respondWithJson($result['data'], $result['message'], $result['status_code']);
    }

    public function getUserTreatmentPackages($userId)
    {
        $userTreatmentPackages = $this->userService->getUserTreatmentPackages($userId);
        return $this->respondWithJson($userTreatmentPackages, 'User treatment packages retrieved successfully');
    }

    public function debugAuth(Request $request)
    {
        return $this->respondWithJson([
            'user' => Auth::user(),
            'authenticated' => Auth::check(),
            'token' => $request->bearerToken(),
        ], 'Debug auth information');
    }

    public function import(ImportUsersRequest $request)
    {
        try {
            $file = $request->file('file');
            $results = $this->userService->importUsers($file);

            Log::info('Import results:', $results);

            if ($results['failed'] > 0) {
                Log::info('Import had failures. Redirecting with errors.');
                Session::flash('importErrors', $this->formatImportErrors($results['failures']));
                Session::flash('importStats', [
                    'total' => $results['total'],
                    'successful' => $results['successful'],
                    'failed' => $results['failed'],
                ]);
                return redirect()->route('users.index');
            }

            Log::info('Import successful. Redirecting with success message.');
            Session::flash('importSuccess', true);
            Session::flash('importStats', [
                'total' => $results['total'],
                'successful' => $results['successful'],
                'failed' => $results['failed'],
            ]);
            return redirect()->route('users.index');
        } catch (\Exception $e) {
            Log::error('Error during import: ' . $e->getMessage());
            Session::flash('error', 'Có lỗi xảy ra khi nhập dữ liệu');
            return redirect()->route('users.index');
        }
    }

    private function formatImportErrors($failures)
    {
        $errorMessages = [];
        foreach ($failures as $failure) {
            $row = $failure->row();
            $attribute = $failure->attribute();
            $errors = $failure->errors();
            $values = $failure->values();

            $errorMessage = "Dòng {$row}: ";
            foreach ($errors as $error) {
                switch ($attribute) {
                    case 'full_name':
                        $errorMessage .= "Tên đầy đủ {$error}. ";
                        break;
                    case 'phone_number':
                        $errorMessage .= "Số điện thoại {$error}. ";
                        break;
                    case 'email':
                        $errorMessage .= "Email {$error}. ";
                        break;
                    case 'gender':
                        $errorMessage .= "Giới tính {$error}. ";
                        break;
                    case 'date_of_birth':
                        $errorMessage .= "Ngày sinh {$error}. ";
                        break;
                    default:
                        $errorMessage .= "{$attribute} {$error}. ";
                }
            }
            $errorMessage .= "Giá trị đã nhập: " . json_encode($values, JSON_UNESCAPED_UNICODE);
            $errorMessages[] = $errorMessage;
        }
        return $errorMessages;
    }
}
