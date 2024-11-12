<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Appointment;
use App\Models\Service;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Contracts\Database\Query\Expression;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $period = $request->input('period', 'week');
            $startDate = $this->getStartDate($period);
            $newUsers = User::where('role', 'user')->latest()->take(5)->get();
            $userCount = User::where('role', 'user')->where('created_at', '>=', $startDate)->count();
            $salesAmount = Invoice::where('created_at', '>=', $startDate)
                ->whereIn('status', ['paid', 'partial'])
                ->sum('paid_amount');
            $orderCount = Order::where('created_at', '>=', $startDate)->count();
            $salesData = $this->getSalesData($startDate, $period);

            // Log response data để debug
            Log::info('Dashboard Response:', [
                'period' => $period,
                'salesData' => $salesData,
                'salesAmount' => $salesAmount,
                'orderCount' => $orderCount,
            ]);

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
            return response()->json([
                'error' => 'An error occurred while fetching dashboard data',
                'message' => $e->getMessage()
            ], 500);
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

            // Debug query
            Log::info('Query parameters:', [
                'start_date' => $startDate->toDateTimeString(),
                'end_date' => $endDate->toDateTimeString(),
                'period' => $period
            ]);

            $query = Invoice::query()
                ->whereBetween('created_at', [$startDate, $endDate])
                ->whereIn('status', ['paid', 'partial'])
                ->whereNull('deleted_at')
                ->select([
                    DB::raw("DATE_FORMAT(created_at, '{$dateFormat}') as date"),
                    DB::raw('COALESCE(SUM(paid_amount), 0) as total_sales')
                ])
                ->groupBy(DB::raw("DATE_FORMAT(created_at, '{$dateFormat}')"))
                ->orderBy(DB::raw("DATE_FORMAT(created_at, '{$dateFormat}')"));

            // Log the actual SQL query
            Log::info('SQL Query:', [
                'sql' => $query->toSql(),
                'bindings' => $query->getBindings()
            ]);

            $result = $query->get();

            // Log the raw result
            Log::info('Raw query result:', ['result' => $result->toArray()]);

            if ($result->isEmpty()) {
                // Nếu không có dữ liệu, tạo dữ liệu mẫu với giá trị 0
                $dates = $this->generateDateRange($startDate, $endDate, $period);
                return array_map(function ($date) {
                    return [
                        'date' => $date,
                        'total_sales' => 0
                    ];
                }, $dates);
            }

            return $result->map(function ($item) {
                return [
                    'date' => $item->date,
                    'total_sales' => (float) $item->total_sales
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
                return DB::raw('DATE_FORMAT(created_at, "%Y-%m")');
            case 'year':
                return DB::raw('DATE_FORMAT(created_at, "%Y-%m")');
            default:
                return DB::raw('DATE(created_at)');
        }
    }
    private function getDateFormat(string $period): string
    {
        switch ($period) {
            case 'week':
            case 'month':
                return '%d/%m';
            case 'quarter':
            case 'year':
                return '%m/%Y';
            default:
                return '%d/%m';
        }
    }
    public function stats(Request $request): JsonResponse
    {
        try {
            $period = $request->input('period', 'week');
            $startDate = $this->getStartDate($period);
            $endDate = Carbon::now();

            // Kiểm tra bảng appointments
            if (!Schema::hasTable('appointments')) {
                return response()->json([
                    'peakHours' => [],
                    'popularServices' => [],
                    'topCustomers' => [],
                    'bestSellingProducts' => [],
                    'lowStockProducts' => [],
                    'cancelledServices' => []
                ]);
            }

            // Thời điểm đông khách
            $peakHours = Appointment::with('timeSlot')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->get()
                ->groupBy(function ($appointment) {
                    return Carbon::parse($appointment->timeSlot->start_time)->format('H');
                })
                ->map(function ($appointments, $hour) {
                    return [
                        'time' => sprintf('%02d:00', $hour),
                        'count' => $appointments->count()
                    ];
                })
                ->sortByDesc('count')
                ->take(5)
                ->values();

            // Dịch vụ phổ biến
            $popularServices = Service::withCount(['appointments' => function ($query) use ($startDate, $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate]);
            }])
                ->orderByDesc('appointments_count')
                ->take(5)
                ->get()
                ->map(function ($service) {
                    return [
                        'id' => $service->id,
                        'name' => $service->name,
                        'bookings' => $service->appointments_count
                    ];
                });

            // Khách hàng thân thiết
            $topCustomers = User::where('role', 'user')
                ->withCount(['appointments' => function ($query) use ($startDate, $endDate) {
                    $query->whereBetween('created_at', [$startDate, $endDate]);
                }])
                ->orderByDesc('appointments_count')
                ->take(5)
                ->get()
                ->map(function ($user) {
                    return [
                        'id' => $user->id,
                        'name' => $user->full_name,
                        'visit_count' => $user->appointments_count,
                        'total_spent' => $user->invoices()->sum('total_amount')
                    ];
                });

            // Sản phẩm bán chạy
            $bestSellingProducts = Product::withSum(['orderItems' => function ($query) use ($startDate, $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate]);
            }], 'quantity')
                ->orderByDesc('order_items_sum_quantity')
                ->take(5)
                ->get()
                ->map(function ($product) {
                    return [
                        'id' => $product->id,
                        'name' => $product->name,
                        'sold' => (int)$product->order_items_sum_quantity ?? 0
                    ];
                });

            // Sản phẩm sắp hết hàng
            $lowStockProducts = Product::withSum(['stockMovements as stock' => function ($query) {
                $query->selectRaw('COALESCE(SUM(CASE WHEN type = ? THEN quantity ELSE -quantity END), 0)', [StockMovement::TYPE_IN]);
            }], 'quantity')
                ->havingRaw('stock <= ?', [10])
                ->orderBy('stock')
                ->take(5)
                ->get()
                ->map(function ($product) {
                    return [
                        'id' => $product->id,
                        'name' => $product->name,
                        'stock' => (int)$product->stock
                    ];
                });

            // Dịch vụ bị hủy nhiều
            $cancelledServices = Service::withCount(['appointments' => function ($query) use ($startDate, $endDate) {
                $query->where('status', 'cancelled')
                    ->whereBetween('created_at', [$startDate, $endDate]);
            }])
                ->orderByDesc('appointments_count')
                ->take(5)
                ->get()
                ->map(function ($service) {
                    return [
                        'id' => $service->id,
                        'name' => $service->name,
                        'cancelled_count' => $service->appointments_count
                    ];
                });

            return response()->json([
                'peakHours' => $peakHours,
                'popularServices' => $popularServices,
                'topCustomers' => $topCustomers,
                'bestSellingProducts' => $bestSellingProducts,
                'lowStockProducts' => $lowStockProducts,
                'cancelledServices' => $cancelledServices
            ]);
        } catch (\Exception $e) {
            Log::error('Stats error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            return response()->json([
                'error' => 'An error occurred while fetching stats data',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // Thêm method để tạo range dates
    private function generateDateRange(Carbon $startDate, Carbon $endDate, string $period): array
    {
        $dates = [];
        $currentDate = clone $startDate;
        $format = $this->getDateFormat($period);

        while ($currentDate <= $endDate) {
            $dates[] = $currentDate->format(str_replace(['%d', '%m', '%Y'], ['d', 'm', 'Y'], $format));

            switch ($period) {
                case 'week':
                case 'month':
                    $currentDate->addDay();
                    break;
                case 'quarter':
                case 'year':
                    $currentDate->addMonth();
                    break;
            }
        }

        return $dates;
    }
}
