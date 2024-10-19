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
use Illuminate\Http\JsonResponse;
use OpenApi\Annotations as OA;
use App\Http\Requests\UpdateUserRequest;

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
     *
     * @param UpdateUserRequest $request
     * @param string $id
     * @return \Illuminate\Http\JsonResponse|\Inertia\Response
     *
     * @OA\Put(
     *     path="/api/users/{id}",
     *     summary="Cập nhật thông tin người dùng",
     *     description="Cập nhật thông tin chi tiết của người dùng",
     *     operationId="updateUser",
     *     tags={"User"},
     *     security={{ "bearerAuth": {} }},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID của người dùng",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="full_name", type="string", example="Nguyễn Văn B"),
     *             @OA\Property(property="phone_number", type="string", example="0987654321"),
     *             @OA\Property(property="email", type="string", example="nguyenvanb@example.com"),
     *             @OA\Property(property="gender", type="string", example="female"),
     *             @OA\Property(property="date_of_birth", type="string", format="date", example="1995-05-15"),
     *             @OA\Property(property="skin_condition", type="string", example="dry")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Thông tin người dùng đã được cập nhật thành công"),
     *             @OA\Property(property="status_code", type="integer", example=200),
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 ref="#/components/schemas/User"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Không tìm thấy người dùng",
     *         @OA\JsonContent(
     *             @OA\Property(property="status_code", type="integer", example=404),
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Không tìm thấy người dùng")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Dữ liệu không hợp lệ",
     *         @OA\JsonContent(
     *             @OA\Property(property="status_code", type="integer", example=422),
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Dữ liệu không hợp lệ"),
     *         )
     *     )
     * )
     */
    public function update(UpdateUserRequest $request, string $id)
    {
        try {
            $user = $this->userService->updateUser($id, $request->validated());

            if ($request->expectsJson()) {
                return $this->respondWithJson($user, 'Thông tin người dùng đã được cập nhật thành công');
            }

            return $this->respondWithInertia('Customers/CustomerDetails', [
                'user' => $user,
                'message' => 'Thông tin người dùng đã được cập nhật thành công'
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->respondWithJson(null, 'Không tìm thấy người dùng', 404);
        } catch (\Exception $e) {
            return $this->respondWithJson(null, 'Có lỗi xảy ra khi cập nhật thông tin người dùng', 500);
        }
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

    /**
     * Get user information for mobile app.
     *
     * @param Request $request
     * @return JsonResponse
     *
     * @OA\Get(
     *     path="/api/user/info",
     *     summary="Lấy thông tin người dùng",
     *     description="Truy xuất thông tin chi tiết của người dùng đã xác thực",
     *     operationId="getUserInfo",
     *     tags={"User"},
     *     security={{ "bearerAuth": {} }},
     *     @OA\Response(
     *         response=200,
     *         description="Thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Thông tin người dùng được truy xuất thành công"),
     *             @OA\Property(property="status_code", type="integer", example=200),
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="string", example="123e4567-e89b-12d3-a456-426614174000"),
     *                 @OA\Property(property="full_name", type="string", example="Nguyễn Văn A"),
     *                 @OA\Property(property="phone_number", type="string", example="0123456789"),
     *                 @OA\Property(property="email", type="string", example="nguyenvana@example.com"),
     *                 @OA\Property(property="gender", type="string", example="male"),
     *                 @OA\Property(property="date_of_birth", type="string", format="date", example="1990-01-01"),
     *                 @OA\Property(property="loyalty_points", type="integer", example=100),
     *                 @OA\Property(property="skin_condition", type="string", example="normal"),
     *                 @OA\Property(property="purchase_count", type="integer", example=5)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Chưa xác thực",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     */
    public function getUserInfo(Request $request)
    {
        $user = $request->user();
        $userInfo = [
            'id' => $user->id,
            'full_name' => $user->full_name,
            'phone_number' => $user->phone_number,
            'email' => $user->email,
            'gender' => $user->gender,
            'date_of_birth' => $user->date_of_birth,
            'loyalty_points' => $user->loyalty_points,
            'skin_condition' => $user->skin_condition,
            'purchase_count' => $user->purchase_count,
        ];

        return $this->respondWithJson($userInfo, 'Thông tin người dùng được truy xuất thành công');
    }
}
