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

    public function getPaginatedServices(int $perPage = 15): LengthAwarePaginator
    {
        return Service::with('category')->paginate($perPage);
    }

    public function getServiceById(int $id): ?Service
    {
        return Service::with('category')->find($id);
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
