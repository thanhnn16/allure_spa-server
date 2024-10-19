<?php

namespace App\Services;

use App\Models\Treatment;
use App\Models\TreatmentCategory;
use App\Models\TreatmentCombo;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class TreatmentService
{
    public function getAllCategories(): Collection
    {
        return TreatmentCategory::with('children', 'treatments')->whereNull('parent_id')->get();
    }

    public function getPaginatedTreatments(int $perPage = 15): LengthAwarePaginator
    {
        return Treatment::with('category')->paginate($perPage);
    }

    public function getTreatmentById(int $id): ?Treatment
    {
        return Treatment::with('category')->find($id);
    }

    public function updateTreatment(int $id, array $data): ?Treatment
    {
        $treatment = Treatment::find($id);
        if (!$treatment) {
            return null;
        }

        $treatment->update($data);
        return $treatment->fresh();
    }

    public function deleteTreatment(int $id): bool
    {
        $treatment = Treatment::find($id);
        if (!$treatment) {
            return false;
        }

        return $treatment->delete();
    }

    public function getTreatmentComboById(int $id): ?TreatmentCombo
    {
        return TreatmentCombo::with(['treatments', 'image'])->find($id);
    }
}
