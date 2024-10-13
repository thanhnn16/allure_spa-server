<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\UsersImport;
use App\Imports\UserTreatmentPackagesImport;
use App\Imports\TreatmentUsageHistoryImport;
use App\Models\User;
use App\Models\UserTreatmentPackage;
use App\Models\TreatmentUsageHistory;
use Illuminate\Support\Facades\DB;
class ImportController extends Controller
{
    public function importAll(Request $request)
    {
        $request->validate([
            'users_file' => 'required|file|mimes:xlsx,xls',
            'treatment_packages_file' => 'nullable|file|mimes:xlsx,xls',
            'usage_history_file' => 'nullable|file|mimes:xlsx,xls',
        ]);
        DB::beginTransaction();
        try {
            // Import Users
            $users = Excel::toCollection(new UsersImport, $request->file('users_file'))->first();
            $userMap = [];
            foreach ($users as $userData) {
                $user = User::create($userData->toArray());
                $userMap[$userData['temp_id']] = $user->id;
            }
            // Import User Treatment Packages if file is provided
            if ($request->hasFile('treatment_packages_file')) {
                $treatmentPackages = Excel::toCollection(new UserTreatmentPackagesImport, $request->file('treatment_packages_file'))->first();
                foreach ($treatmentPackages as $packageData) {
                    $userId = $userMap[$packageData['temp_id']] ?? null;
                    if ($userId) {
                        $package = new UserTreatmentPackage($packageData->toArray());
                        $package->user_id = $userId;
                        $package->save();
                    }
                }
            }
            // Import Treatment Usage History if file is provided
            if ($request->hasFile('usage_history_file')) {
                $usageHistories = Excel::toCollection(new TreatmentUsageHistoryImport, $request->file('usage_history_file'))->first();
                foreach ($usageHistories as $historyData) {
                    $userId = $userMap[$historyData['temp_id']] ?? null;
                    if ($userId) {
                        $history = new TreatmentUsageHistory($historyData->toArray());
                        $history->user_id = $userId;
                        $history->save();
                    }
                }
            }
            DB::commit();
            return response()->json(['message' => 'Import successful'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Import failed', 'error' => $e->getMessage()], 500);
        }
    }
    public function importUsers(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls',
        ]);
        DB::beginTransaction();
        try {
            Excel::import(new UsersImport, $request->file('file'));
            DB::commit();
            return response()->json(['message' => 'Users import successful'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Users import failed', 'error' => $e->getMessage()], 500);
        }
    }
    public function importTreatmentPackages(Request $request)
    {
        $request->validate([
            'treatment_packages_file' => 'required|file|mimes:xlsx,xls',
        ]);
        DB::beginTransaction();
        try {
            $treatmentPackages = Excel::toCollection(new UserTreatmentPackagesImport, $request->file('treatment_packages_file'))->first();
            foreach ($treatmentPackages as $packageData) {
                UserTreatmentPackage::create($packageData->toArray());
            }
            DB::commit();
            return response()->json(['message' => 'Treatment packages import successful'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Treatment packages import failed', 'error' => $e->getMessage()], 500);
        }
    }
    public function importUsageHistory(Request $request)
    {
        $request->validate([
            'usage_history_file' => 'required|file|mimes:xlsx,xls',
        ]);
        DB::beginTransaction();
        try {
            $usageHistories = Excel::toCollection(new TreatmentUsageHistoryImport, $request->file('usage_history_file'))->first();
            foreach ($usageHistories as $historyData) {
                TreatmentUsageHistory::create($historyData->toArray());
            }
            DB::commit();
            return response()->json(['message' => 'Usage history import successful'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Usage history import failed', 'error' => $e->getMessage()], 500);
        }
    }
}