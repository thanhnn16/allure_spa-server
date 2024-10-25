<?php

namespace App\Services;

use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServiceCombo;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class ServiceService
{
    public function getAllCategories(): Collection
    {
        return ServiceCategory::with('children', 'services')->whereNull('parent_id')->get();
    }

    public function getPaginatedServices(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = Service::with('category');

        if (!empty($filters['search'])) {
            $query->where('name', 'like', '%' . $filters['search'] . '%');
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

    public function getServiceById(int $id): ?Service
    {
        return Service::with([
            'category',
            'media',
            'priceHistory' => function ($query) {
                $query->orderBy('effective_from', 'desc');
            }
        ])->find($id);
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

    public function getServiceComboById(int $id): ?ServiceCombo
    {
        return ServiceCombo::with(['services', 'image'])->find($id);
    }
}
