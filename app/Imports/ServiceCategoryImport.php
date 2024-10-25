<?php

namespace App\Imports;

use App\Models\ServiceCategory;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ServiceCategoryImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        return new ServiceCategory([
            'category_name' => $row['category_name'],
            'parent_id' => $row['parent_id'] ?: null,
            'created_at' => $row['created_at'] ?: now(),
            'updated_at' => $row['updated_at'] ?: now(),
        ]);
    }
}
