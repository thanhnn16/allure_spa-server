<?php

namespace App\Imports;

use App\Models\UserServicePackage;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class UserServicePackagesImport implements ToModel, WithHeadingRow
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        return new UserServicePackage([
            'phone_number'             => $row['phone_number'],
            'service_combo_id'  => $row['service_combo_id'],
            'purchase_date'       => Date::excelToDateTimeObject($row['purchase_date']),
            'total_sessions'      => $row['total_sessions'] ?? 0,
            'remaining_sessions'  => $row['remaining_sessions'] ?? 0,
            'expiry_date'         => Date::excelToDateTimeObject($row['expiry_date']),
        ]);
    }
}
