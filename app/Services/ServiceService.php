<?php

namespace App\Services;

use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServiceCombo;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class ServiceService
{
    public function getAllCategories(): Collection
    {
        return ServiceCategory::with('children', 'services')->whereNull('parent_id')->get();
    }

    public function getPaginatedServices(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = Service::with([
            'category',
            'media' // Media model đã có $appends = ['full_url'] nên sẽ tự động thêm full_url
        ]);

        if (!empty($filters['search'])) {
            $query->where('service_name', 'like', '%' . $filters['search'] . '%');
        }

        if (!empty($filters['category'])) {
            $query->where('category_id', $filters['category']);
        }

        if (!empty($filters['sort'])) {
            $direction = $filters['direction'] ?? 'asc';
            $query->orderBy($filters['sort'], $direction);
        }

        return $query->paginate($perPage);
    }

    public function getServiceById($id)
    {
        $query = Service::with([
            'category',
            'media',
            'priceHistory' => function ($query) {
                $query->orderBy('effective_from', 'desc');
            },
            'ratings' => function ($query) {
                $query->where('status', 'approved')
                    ->where('rating_type', 'service');
            },
            'favorites' // Tải trước quan hệ favorites
        ])
            ->withCount(['ratings as total_ratings' => function ($query) {
                $query->where('status', 'approved')
                    ->where('rating_type', 'service');
            }])
            ->withAvg(['ratings as average_rating' => function ($query) {
                $query->where('status', 'approved')
                    ->where('rating_type', 'service');
            }], 'stars');

        if (Auth::check()) {
            $userId = Auth::id();
            $query->withCount(['favorites as favorites_count' => function ($query) use ($userId) {
                $query->where('user_id', $userId)
                    ->where('favorite_type', 'service');
            }]);
        }

        return $query->findOrFail($id);
    }

    public function updateService(int $id, array $data): ?Service
    {
        $service = Service::find($id);
        if (!$service) {
            return null;
        }

        $service->update($data);
        return $service->fresh();
    }

    public function deleteService(int $id): bool
    {
        $service = Service::find($id);
        if (!$service) {
            return false;
        }

        return $service->delete();
    }
}
