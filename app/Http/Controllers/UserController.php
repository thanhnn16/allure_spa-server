<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\UserService;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

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
        //
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
}
