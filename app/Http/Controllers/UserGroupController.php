<?php

namespace App\Http\Controllers;

use App\Models\UserGroup;
use App\Models\UserGroupCondition;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserGroupController extends BaseController
{
    public function index()
    {
        return inertia('MobileApp/UserGroups/Index');
    }

    public function getGroups()
    {
        try {
            $groups = UserGroup::with(['conditions'])
                ->get()
                ->map(function ($group) {
                    return [
                        'id' => $group->id,
                        'name' => $group->name,
                        'description' => $group->description,
                        'conditions' => $group->conditions,
                        'user_count' => $group->users_count
                    ];
                });

            return $this->respondWithJson($groups);
        } catch (\Exception $e) {
            return $this->respondWithError('Lỗi khi lấy danh sách nhóm: ' . $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'conditions' => 'required|array|min:1',
                'conditions.*.field' => 'required|string',
                'conditions.*.operator' => 'required|string',
                'conditions.*.value' => 'required|string'
            ]);

            $group = UserGroup::create([
                'name' => $validated['name'],
                'description' => $validated['description']
            ]);

            foreach ($validated['conditions'] as $condition) {
                $group->conditions()->create($condition);
            }

            // Cập nhật danh sách users thuộc nhóm
            $users = $group->getFilteredUsers();
            $group->users()->sync($users->pluck('id'));

            DB::commit();

            return $this->respondWithJson($group->load('conditions'), 'Tạo nhóm thành công');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->respondWithError('Lỗi khi tạo nhóm: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            $group = UserGroup::findOrFail($id);

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'conditions' => 'required|array|min:1',
                'conditions.*.field' => 'required|string',
                'conditions.*.operator' => 'required|string',
                'conditions.*.value' => 'required|string'
            ]);

            $group->update([
                'name' => $validated['name'],
                'description' => $validated['description']
            ]);

            // Xóa điều kiện cũ và tạo mới
            $group->conditions()->delete();
            foreach ($validated['conditions'] as $condition) {
                $group->conditions()->create($condition);
            }

            // Cập nhật lại danh sách users
            $users = $group->getFilteredUsers();
            $group->users()->sync($users->pluck('id'));

            DB::commit();

            return $this->respondWithJson($group->load('conditions'), 'Cập nhật nhóm thành công');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->respondWithError('Lỗi khi cập nhật nhóm: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $group = UserGroup::findOrFail($id);

            // Xóa các liên kết với users
            $group->users()->detach();

            // Xóa các điều kiện
            $group->conditions()->delete();

            // Xóa nhóm
            $group->delete();

            return $this->respondWithJson([], 'Xóa nhóm thành công');
        } catch (\Exception $e) {
            return $this->respondWithError('Lỗi khi xóa nhóm: ' . $e->getMessage());
        }
    }

    public function getGroupUsers($id)
    {
        try {
            $group = UserGroup::findOrFail($id);
            $users = $group->getFilteredUsers();

            return $this->respondWithJson($users);
        } catch (\Exception $e) {
            return $this->respondWithError('Lỗi khi lấy danh sách người dùng: ' . $e->getMessage());
        }
    }

    public function syncGroupUsers($id)
    {
        try {
            DB::beginTransaction();

            $group = UserGroup::findOrFail($id);
            $users = $group->getFilteredUsers();
            $group->users()->sync($users->pluck('id'));

            DB::commit();

            return $this->respondWithJson([], 'Đồng bộ thành viên nhóm thành công');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->respondWithError('Lỗi khi đồng bộ th��nh viên nhóm: ' . $e->getMessage());
        }
    }

    public function syncAllGroups()
    {
        try {
            DB::beginTransaction();
            
            $groups = UserGroup::all();
            $results = [];
            
            foreach ($groups as $group) {
                $userCount = $group->syncUsers();
                $results[] = [
                    'group_id' => $group->id,
                    'name' => $group->name,
                    'user_count' => $userCount
                ];
            }
            
            DB::commit();
            return $this->respondWithJson($results, 'Đồng bộ tất cả nhóm thành công');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->respondWithError('Lỗi khi đồng bộ nhóm: ' . $e->getMessage());
        }
    }

    public function getGroupStats($id)
    {
        try {
            $group = UserGroup::findOrFail($id);
            $users = $group->getFilteredUsers();
            
            $stats = [
                'total_users' => $users->count(),
                'gender_distribution' => $users->groupBy('gender')->map->count(),
                'age_distribution' => $users->groupBy(function($user) {
                    $age = $user->date_of_birth ? $user->date_of_birth->age : null;
                    if (!$age) return 'Không xác định';
                    if ($age < 18) return 'Dưới 18';
                    if ($age < 30) return '18-29';
                    if ($age < 50) return '30-49';
                    return '50+';
                })->map->count(),
                'loyalty_points_avg' => $users->avg('loyalty_points'),
                'purchase_count_avg' => $users->avg('purchase_count')
            ];
            
            return $this->respondWithJson($stats);
        } catch (\Exception $e) {
            return $this->respondWithError('Lỗi khi lấy thống kê nhóm: ' . $e->getMessage());
        }
    }
}
