<?php

namespace App\Imports;

use App\Models\Treatment;
use App\Models\TreatmentTranslation;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class TreatmentImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        $treatment = Treatment::create([
            'category_id' => $row['category_id'],
            'treatment_name' => $row['treatment_name'],
            'description' => $row['description'],
            'duration' => $row['duration'],
            'price' => $row['price'],
            'image_id' => $row['image_id'] ?: null,
            'created_at' => $row['created_at'] ?: now(),
            'updated_at' => $row['updated_at'] ?: now(),
        ]);

        if ($row['language'] && $row['language'] !== 'vi') {
            TreatmentTranslation::create([
                'treatment_id' => $treatment->id,
                'language' => $row['language'],
                'treatment_name' => $row['translated_treatment_name'],
                'description' => $row['translated_description'],
            ]);
        }

        return $treatment;
    }
}