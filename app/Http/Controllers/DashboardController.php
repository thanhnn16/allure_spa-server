<?php
namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Invoice;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Contracts\Database\Query\Expression;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
class DashboardController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $period = $request->input('period', 'week');
            $startDate = $this->getStartDate($period);
            $newUsers = User::where('role', 'user')->latest()->take(5)->get();
            $userCount = User::where('role', 'user')->where('created_at', '>=', $startDate)->count();
            $salesAmount = Invoice::where('created_at', '>=', $startDate)->sum('total_amount');
            $orderCount = Order::where('created_at', '>=', $startDate)->count();
            $salesData = $this->getSalesData($startDate, $period);
            return response()->json([
                'newUsers' => $newUsers,
                'userCount' => $userCount,
                'salesAmount' => $salesAmount,
                'orderCount' => $orderCount,
                'salesData' => $salesData,
            ]);
        } catch (\Exception $e) {
            Log::error('Dashboard error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json(['error' => 'An error occurred while fetching dashboard data: ' . $e->getMessage()], 500);
        }
    }
    private function getStartDate($period): Carbon
    {
        $now = Carbon::now();
        switch ($period) {
            case 'week':
                return $now->startOfWeek();
            case 'month':
                return $now->startOfMonth();
            case 'quarter':
                return $now->startOfQuarter();
            case 'year':
                return $now->startOfYear();
            default:
                return $now->startOfWeek();
        }
    }
    private function getSalesData(Carbon $startDate, string $period): array
    {
        try {
            $endDate = Carbon::now();
            $groupBy = $this->getGroupBy($period);
            $dateFormat = $this->getDateFormat($period);
            $result = Invoice::whereBetween('created_at', [$startDate, $endDate])
                ->groupBy($groupBy)
                ->orderBy($groupBy)
                ->get([
                    DB::raw("DATE_FORMAT(created_at, '{$dateFormat}') as date"),
                    DB::raw('SUM(total_amount) as total_sales')
                ]);
            return $result->map(function ($item) {
                return [
                    'date' => $item->date,
                    'total_sales' => (float) $item->total_sales,
                ];
            })->values()->toArray();
        } catch (\Exception $e) {
            Log::error('Error in getSalesData: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return [];
        }
    }
    private function getGroupBy(string $period): Expression
    {
        switch ($period) {
            case 'week':
            case 'month':
                return DB::raw('DATE(created_at)');
            case 'quarter':
                return DB::raw('MONTH(created_at)');
            case 'year':
                return DB::raw('YEAR(created_at)');
            default:
                return DB::raw('DATE(created_at)');
        }
    }
    private function getDateFormat(string $period): string
    {
        switch ($period) {
            case 'week':
            case 'month':
                return '%Y-%m-%d';
            case 'quarter':
            case 'year':
                return '%Y-%m';
            default:
                return '%Y-%m-%d';
        }
    }
}
