<?php

namespace App\Imports;

use App\Models\ServiceUsageHistory;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class ServiceUsageHistoryImport implements ToModel, WithHeadingRow
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        return new ServiceUsageHistory([
            'user_service_package_id' => $row['user_service_package_id'],
            'service_date'            => Date::excelToDateTimeObject($row['service_date']),
            'staff_id'                  => $row['staff_id'],
            'notes'                     => $row['notes'],
            'session_result'            => $row['session_result'],
        ]);
    }
}
