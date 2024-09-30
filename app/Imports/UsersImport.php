<?php

namespace App\Imports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Hash;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class UsersImport implements ToModel, WithHeadingRow
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        return new User([
            'phone_number'  => $row['phone_number'],
            'email'         => $row['email'],
            'password'      => Hash::make('allurespa'),
            'role'          => $row['role'] ?? 'user',
            'full_name'     => $row['full_name'],
            'gender'        => $row['gender'] ?? 'other',
            'date_of_birth' => Date::excelToDateTimeObject($row['date_of_birth']),
            'loyalty_points'         => $row['loyalty_points'] ?? 0,
            'skin_condition'         => $row['skin_condition'] ?? null,
            'note'          => $row['note'] ?? null,
            'purchase_count' => $row['purchase_count'] ?? 0,
        ]);
    }
}
