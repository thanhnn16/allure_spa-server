<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Service;
use App\Services\SearchService;
use App\Services\AppointmentService;
use App\Http\Controllers\TimeSlotController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Invoice;
use App\Models\Favorite;
use App\Models\UserVoucher;
use Carbon\Carbon;

class AiFunctionCallingService
{
    protected $searchService;
    protected $appointmentService;
    protected $timeSlotController;

    public function __construct(
        SearchService $searchService,
        AppointmentService $appointmentService,
        TimeSlotController $timeSlotController
    ) {
        $this->searchService = $searchService;
        $this->appointmentService = $appointmentService;
        $this->timeSlotController = $timeSlotController;
    }

    public function handleFunctionCall($functionName, $args)
    {
        try {
            // Kiểm tra xem $args có tồn tại và là array không
            if (!is_array($args)) {
                $args = [];
            }
            
            switch ($functionName) {
                case 'getAllProducts':
                    return $this->handleGetAllProducts();
                case 'getAllServices': 
                    return $this->handleGetAllServices();
                case 'getAvailableTimeSlots':
                    // Kiểm tra tham số trước khi gọi
                    if (!isset($args['date'])) {
                        throw new \InvalidArgumentException("Thiếu tham số bắt buộc: date");
                    }
                    return $this->handleGetTimeSlots($args);
                case 'createAppointment':
                    // Kiểm tra các tham số bắt buộc
                    $requiredFields = ['service_id', 'appointment_date', 'time_slot_id', 'appointment_type'];
                    foreach ($requiredFields as $field) {
                        if (!isset($args[$field])) {
                            throw new \InvalidArgumentException("Thiếu tham số bắt buộc: {$field}");
                        }
                    }
                    return $this->handleCreateAppointment($args);
                case 'getProductDetails':
                    if (!isset($args['product_id'])) {
                        throw new \InvalidArgumentException("Thiếu tham số bắt buộc: product_id");
                    }
                    return $this->handleProductDetails($args);
                case 'getServiceDetails':
                    if (!isset($args['service_id'])) {
                        throw new \InvalidArgumentException("Thiếu tham số bắt buộc: service_id"); 
                    }
                    return $this->handleServiceDetails($args);
                case 'getUserVouchers':
                    return $this->handleGetUserVouchers($args);
                case 'getUserInvoices':
                    return $this->handleGetUserInvoices($args);
                case 'getUserFavorites':
                    if (!isset($args['type'])) {
                        throw new \InvalidArgumentException("Thiếu tham số bắt buộc: type");
                    }
                    return $this->handleGetUserFavorites($args);
                default:
                    throw new \Exception("Không tìm thấy hàm: {$functionName}");
            }
        } catch (\Exception $e) {
            Log::error("Lỗi gọi hàm: {$e->getMessage()}", [
                'function' => $functionName,
                'arguments' => $args,
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    protected function handleGetAllProducts()
    {
        try {
            $products = Product::with(['category', 'media', 'attributes', 'ratings'])->get();

            $formattedData = $products->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => $product->price,
                    'category' => $product->category ? $product->category->name : null,
                    'brand_description' => $product->brand_description,
                    'usage' => $product->usage,
                    'benefits' => $product->benefits,
                    'key_ingredients' => $product->key_ingredients,
                    'ingredients' => $product->ingredients,
                    'directions' => $product->directions,
                    'storage_instructions' => $product->storage_instructions,
                    'product_notes' => $product->product_notes,
                    'images' => $product->media ? $product->media->pluck('url') : [],
                    'attributes' => $product->attributes ? $product->attributes->pluck('attribute_value', 'name') : [],
                    'rating' => [
                        'average' => $product->ratings ? round($product->ratings()->avg('rating'), 1) : 0,
                        'count' => $product->ratings ? $product->ratings()->count() : 0
                    ]
                ];
            });

            return [
                'success' => true,
                'data' => $formattedData
            ];
        } catch (\Exception $e) {
            Log::error("Get all products error: " . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    protected function handleGetAllServices()
    {
        try {
            $services = Service::with(['category', 'media', 'ratings'])->get();

            $formattedData = $services->map(function ($service) {
                return [
                    'id' => $service->id,
                    'name' => $service->service_name,
                    'description' => $service->description,
                    'duration' => $service->duration,
                    'prices' => [
                        'single' => $service->single_price,
                        'combo_5' => $service->combo_5_price,
                        'combo_10' => $service->combo_10_price
                    ],
                    'validity_period' => $service->validity_period,
                    'category' => $service->category->name,
                    'images' => $service->media->pluck('url'),
                    'rating' => [
                        'average' => round($service->ratings()->avg('rating'), 1),
                        'count' => $service->ratings()->count()
                    ]
                ];
            });

            return [
                'success' => true,
                'data' => $formattedData
            ];
        } catch (\Exception $e) {
            Log::error("Get all services error: {$e->getMessage()}");
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    protected function handleGetTimeSlots($args)
    {
        try {
            // Validate required arguments
            if (!isset($args['date'])) {
                throw new \InvalidArgumentException("Missing required argument: date");
            }

            $date = date('Y-m-d', strtotime($args['date']));

            $request = new \Illuminate\Http\Request();
            $request->query->set('date', $date);

            return [
                'success' => true,
                'data' => $this->timeSlotController->getAvailableSlots($request)
            ];
        } catch (\Exception $e) {
            Log::error("Time slots error: {$e->getMessage()}");
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    protected function handleCreateAppointment($args)
    {
        try {
            // Validate required arguments
            $requiredFields = ['service_id', 'appointment_date', 'time_slot_id', 'appointment_type'];
            foreach ($requiredFields as $field) {
                if (!isset($args[$field])) {
                    throw new \InvalidArgumentException("Missing required argument: {$field}");
                }
            }

            // Add current user ID from context
            $args['user_id'] = Auth::user()->id;

            $result = $this->appointmentService->createAppointment($args);

            return [
                'success' => $result['success'] ?? ($result['status'] === 200),
                'data' => $result['data'],
                'message' => $result['message']
            ];
        } catch (\Exception $e) {
            Log::error("Create appointment error: {$e->getMessage()}");
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }


    protected function handleProductDetails($args)
    {
        try {
            // Validate required arguments
            if (!isset($args['product_id'])) {
                throw new \InvalidArgumentException("Missing required argument: product_id");
            }

            $product = Product::with(['category', 'media', 'attributes', 'ratings'])
                ->findOrFail($args['product_id']);

            // Format response data
            $formattedData = [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'category' => $product->category->name,
                'brand_description' => $product->brand_description,
                'usage' => $product->usage,
                'benefits' => $product->benefits,
                'key_ingredients' => $product->key_ingredients,
                'ingredients' => $product->ingredients,
                'directions' => $product->directions,
                'storage_instructions' => $product->storage_instructions,
                'product_notes' => $product->product_notes,
                'images' => $product->media->pluck('url'),
                'attributes' => $product->attributes->pluck('attribute_value', 'name'),
                'rating' => [
                    'average' => round($product->ratings()->avg('rating'), 1),
                    'count' => $product->ratings()->count()
                ]
            ];

            return [
                'success' => true,
                'data' => $formattedData
            ];
        } catch (\Exception $e) {
            Log::error("Product details error: {$e->getMessage()}");
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    protected function handleServiceDetails($args)
    {
        try {
            // Validate required arguments
            if (!isset($args['service_id'])) {
                throw new \InvalidArgumentException("Missing required argument: service_id");
            }

            $service = Service::with(['category', 'media', 'ratings'])
                ->findOrFail($args['service_id']);

            // Format response data
            $formattedData = [
                'id' => $service->id,
                'name' => $service->service_name,
                'description' => $service->description,
                'duration' => $service->duration,
                'prices' => [
                    'single' => $service->single_price,
                    'combo_5' => $service->combo_5_price,
                    'combo_10' => $service->combo_10_price
                ],
                'validity_period' => $service->validity_period,
                'category' => $service->category->name,
                'images' => $service->media->pluck('url'),
                'rating' => [
                    'average' => round($service->ratings()->avg('rating'), 1),
                    'count' => $service->ratings()->count()
                ]
            ];

            return [
                'success' => true,
                'data' => $formattedData
            ];
        } catch (\Exception $e) {
            Log::error("Service details error: {$e->getMessage()}");
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    protected function handleGetUserVouchers($args)
    {
        try {
            // Lấy user_id từ args hoặc từ Auth::id()
            $userId = $args['user_id'] ?? Auth::id();
            if (!$userId) {
                throw new \InvalidArgumentException("User ID is required");
            }

            $query = UserVoucher::with(['voucher'])
                ->where('user_id', $userId);

            if (isset($args['status'])) {
                $now = Carbon::now();

                switch ($args['status']) {
                    case 'active':
                        $query->whereHas('voucher', function ($q) use ($now) {
                            $q->where('end_date', '>=', $now)
                                ->where('start_date', '<=', $now);
                        })->where('remaining_uses', '>', 0);
                        break;
                    case 'expired':
                        $query->where(function ($q) use ($now) {
                            $q->whereHas('voucher', function ($sq) use ($now) {
                                $sq->where('end_date', '<', $now);
                            })->orWhere('remaining_uses', 0);
                        });
                        break;
                }
            }

            $vouchers = $query->get()->map(function ($userVoucher) {
                $voucher = $userVoucher->voucher;
                return [
                    'id' => $voucher->id,
                    'code' => $voucher->code,
                    'description' => $voucher->description,
                    'discount_type' => $voucher->discount_type,
                    'discount_value' => $voucher->discount_value,
                    'start_date' => $voucher->start_date,
                    'end_date' => $voucher->end_date,
                    'remaining_uses' => $userVoucher->remaining_uses,
                    'total_uses' => $userVoucher->total_uses,
                    'status' => Carbon::now()->between(
                        $voucher->start_date,
                        $voucher->end_date
                    ) && $userVoucher->remaining_uses > 0 ? 'active' : 'expired'
                ];
            });

            return [
                'success' => true,
                'data' => $vouchers,
                'count' => $vouchers->count()
            ];
        } catch (\Exception $e) {
            Log::error("Get user vouchers error: {$e->getMessage()}");
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    protected function handleGetUserInvoices($args)
    {
        try {
            $query = Invoice::with(['items', 'payment'])
                ->where('user_id', Auth::id());

            // Filter by status
            if (isset($args['status']) && $args['status'] !== 'all') {
                $query->where('status', $args['status']);
            }

            // Filter by date range
            if (isset($args['from_date'])) {
                $query->where('created_at', '>=', $args['from_date']);
            }
            if (isset($args['to_date'])) {
                $query->where('created_at', '<=', $args['to_date']);
            }

            $invoices = $query->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($invoice) {
                    return [
                        'id' => $invoice->id,
                        'invoice_number' => $invoice->invoice_number,
                        'total_amount' => $invoice->total_amount,
                        'status' => $invoice->status,
                        'created_at' => $invoice->created_at,
                        'items' => $invoice->items->map(function ($item) {
                            return [
                                'name' => $item->name,
                                'quantity' => $item->quantity,
                                'price' => $item->price,
                                'subtotal' => $item->subtotal
                            ];
                        }),
                        'payment' => $invoice->payment ? [
                            'method' => $invoice->payment->payment_method,
                            'status' => $invoice->payment->status,
                            'paid_at' => $invoice->payment->paid_at
                        ] : null
                    ];
                });

            return [
                'success' => true,
                'data' => $invoices,
                'count' => $invoices->count()
            ];
        } catch (\Exception $e) {
            Log::error("Get user invoices error: {$e->getMessage()}");
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    protected function handleGetUserFavorites($args)
    {
        try {
            if (!isset($args['type'])) {
                throw new \InvalidArgumentException("Missing required argument: type");
            }

            $query = Favorite::where('user_id', Auth::id());

            switch ($args['type']) {
                case 'products':
                    $query->whereNotNull('product_id')
                        ->with(['product.media', 'product.category']);
                    break;
                case 'services':
                    $query->whereNotNull('service_id')
                        ->with(['service.media', 'service.category']);
                    break;
                case 'all':
                    $query->with([
                        'product.media',
                        'product.category',
                        'service.media',
                        'service.category'
                    ]);
                    break;
                default:
                    throw new \InvalidArgumentException("Invalid type: {$args['type']}");
            }

            $favorites = $query->get()->map(function ($favorite) {
                if ($favorite->product) {
                    return [
                        'type' => 'product',
                        'id' => $favorite->product->id,
                        'name' => $favorite->product->name,
                        'price' => $favorite->product->price,
                        'category' => $favorite->product->category->name,
                        'image' => $favorite->product->media->first()?->url,
                        'added_at' => $favorite->created_at
                    ];
                }
                if ($favorite->service) {
                    return [
                        'type' => 'service',
                        'id' => $favorite->service->id,
                        'name' => $favorite->service->service_name,
                        'price' => $favorite->service->single_price,
                        'category' => $favorite->service->category->name,
                        'image' => $favorite->service->media->first()?->url,
                        'added_at' => $favorite->created_at
                    ];
                }
            })->filter();

            return [
                'success' => true,
                'data' => $favorites,
                'count' => $favorites->count()
            ];
        } catch (\Exception $e) {
            Log::error("Get user favorites error: {$e->getMessage()}");
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}
