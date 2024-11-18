<?php

namespace App\Services;

use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServiceCombo;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

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
            'media',
            'translations'
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

    public function getServiceById($id, $userId = null)
    {
        Log::info('Getting service by ID:', [
            'service_id' => $id,
            'user_id' => $userId,
            'is_authenticated' => Auth::check()
        ]);

        $query = Service::with([
            'category',
            'media',
            'priceHistory',
            'translations',
            'ratings' => function ($query) {
                $query->where('status', 'approved')
                    ->where('rating_type', 'service');
            }
        ])
        ->withCount(['ratings as total_ratings' => function ($query) {
            $query->where('status', 'approved')
                ->where('rating_type', 'service');
        }])
        ->withAvg(['ratings as average_rating' => function ($query) {
            $query->where('status', 'approved')
                ->where('rating_type', 'service');
        }], 'stars');

        $service = $query->findOrFail($id);
        
        // Set user_id vào service instance để dùng trong getIsFavoriteAttribute
        $service->current_user_id = $userId;

        return $service;
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
