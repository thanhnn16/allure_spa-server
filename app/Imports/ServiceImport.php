<?php

namespace App\Imports;

use App\Models\Service;
use App\Models\ServiceTranslation;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ServiceImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        $service = Service::create([
            'category_id' => $row['category_id'],
            'service_name' => $row['service_name'],
            'description' => $row['description'],
            'duration' => $row['duration'],
            'price' => $row['price'],
            'image_id' => $row['image_id'] ?: null,
            'created_at' => $row['created_at'] ?: now(),
            'updated_at' => $row['updated_at'] ?: now(),
        ]);

        if ($row['language'] && $row['language'] !== 'vi') {
            ServiceTranslation::create([
                'service_id' => $service->id,
                'language' => $row['language'],
                'service_name' => $row['translated_service_name'],
                'description' => $row['translated_description'],
            ]);
        }

        return $service;
    }
}