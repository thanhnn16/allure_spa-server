<?php

namespace App\Imports;

use App\Models\TreatmentUsageHistory;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class TreatmentUsageHistoryImport implements ToModel, WithHeadingRow
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        return new TreatmentUsageHistory([
            'user_treatment_package_id' => $row['user_treatment_package_id'],
            'treatment_date'            => Date::excelToDateTimeObject($row['treatment_date']),
            'staff_id'                  => $row['staff_id'],
            'notes'                     => $row['notes'],
            'session_result'            => $row['session_result'],
        ]);
    }
}
