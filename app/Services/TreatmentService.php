<?php

namespace App\Services;

use App\Models\Treatment;
use App\Models\TreatmentCategory;
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
}
