<?php

namespace App\Imports;

use App\Models\ServiceCombo;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ServiceComboImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        return new ServiceCombo([
            'service_id' => $row['service_id'],
            'duration' => $row['duration'],
            'combo_type' => $row['combo_type'],
            'combo_price' => $row['combo_price'],
            'is_default' => $row['is_default'],
            'validity_period' => $row['validity_period'],
            'created_at' => $row['created_at'] ?: now(),
            'updated_at' => $row['updated_at'] ?: now(),
        ]);
    }
}