<?php

namespace App\Imports;

use App\Models\TreatmentCombo;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class TreatmentComboImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        return new TreatmentCombo([
            'treatment_id' => $row['treatment_id'],
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