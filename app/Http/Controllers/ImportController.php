<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\TreatmentCategoryImport;
use App\Imports\TreatmentImport;
use App\Imports\TreatmentComboImport;

class ImportController extends Controller
{
    public function treatmentImport(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls',
        ]);

        $file = $request->file('file');

        try {
            Excel::import(new TreatmentCategoryImport, $file, null, \Maatwebsite\Excel\Excel::XLSX, ['sheet' => 0]);
            Excel::import(new TreatmentImport, $file, null, \Maatwebsite\Excel\Excel::XLSX, ['sheet' => 1]);
            Excel::import(new TreatmentComboImport, $file, null, \Maatwebsite\Excel\Excel::XLSX, ['sheet' => 2]);

            return redirect()->back()->with('success', 'Dữ liệu đã được import thành công.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Có lỗi xảy ra khi import dữ liệu: ' . $e->getMessage());
        }
    }
}
