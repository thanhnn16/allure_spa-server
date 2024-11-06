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
            switch ($functionName) {
                case 'search':
                    return $this->handleSearch($args);
                case 'getAvailableTimeSlots':
                    return $this->handleGetTimeSlots($args);
                case 'createAppointment':
                    return $this->handleCreateAppointment($args);
                case 'getProductRecommendations':
                    return $this->handleProductRecommendations($args);
                case 'getServiceRecommendations':
                    return $this->handleServiceRecommendations($args);
                case 'getProductDetails':
                    return $this->handleProductDetails($args);
                case 'getServiceDetails':
                    return $this->handleServiceDetails($args);
                default:
                    throw new \Exception("Unknown function: {$functionName}");
            }
        } catch (\Exception $e) {
            Log::error("Function calling error: {$e->getMessage()}");
            throw $e;
        }
    }

    protected function handleSearch($args)
    {
        try {
            $results = $this->searchService->search(
                $args['query'],
                $args['type'],
                $args['limit'] ?? 10
            );

            return [
                'success' => true,
                'data' => $results
            ];
        } catch (\Exception $e) {
            Log::error("Search error: {$e->getMessage()}");
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    protected function handleGetTimeSlots($args)
    {
        try {
            $slots = $this->timeSlotController->getAvailableSlots($args['date']);
            
            return [
                'success' => true,
                'data' => $slots
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
        // Add current user ID from context
        $args['user_id'] = Auth::user()->id;
        
        return $this->appointmentService->createAppointment($args);
    }

    protected function handleProductRecommendations($args)
    {
        try {
            $query = Product::query()
                ->with(['category', 'media', 'attributes']);

            // Lọc theo loại da
            if (isset($args['skin_type'])) {
                $query->whereHas('attributes', function (Builder $query) use ($args) {
                    $query->where('name', 'skin_type')
                        ->where('attribute_value', $args['skin_type']);
                });
            }

            // Lọc theo vấn đề về da
            if (!empty($args['concerns'])) {
                $query->where(function ($q) use ($args) {
                    foreach ($args['concerns'] as $concern) {
                        $q->orWhere('benefits', 'LIKE', "%{$concern}%")
                          ->orWhere('key_ingredients', 'LIKE', "%{$concern}%");
                    }
                });
            }

            // Lọc theo khoảng giá
            if (isset($args['price_range'])) {
                if (isset($args['price_range']['min'])) {
                    $query->where('price', '>=', $args['price_range']['min']);
                }
                if (isset($args['price_range']['max'])) {
                    $query->where('price', '<=', $args['price_range']['max']);
                }
            }

            // Lọc theo danh mục
            if (isset($args['category_id'])) {
                $query->where('category_id', $args['category_id']);
            }

            $products = $query->get()->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => $product->price,
                    'category' => $product->category->name,
                    'benefits' => $product->benefits,
                    'key_ingredients' => $product->key_ingredients,
                    'usage' => $product->usage,
                    'images' => $product->media->pluck('url'),
                    'attributes' => $product->attributes->pluck('attribute_value', 'name')
                ];
            });

            return [
                'success' => true,
                'data' => $products,
                'count' => $products->count()
            ];
        } catch (\Exception $e) {
            Log::error("Product recommendations error: {$e->getMessage()}");
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    protected function handleServiceRecommendations($args)
    {
        try {
            $query = Service::query()
                ->with(['category', 'media']);

            // Lọc theo loại điều trị
            if (isset($args['treatment_type'])) {
                $query->whereHas('category', function ($q) use ($args) {
                    $q->where('name', 'LIKE', "%{$args['treatment_type']}%");
                });
            }

            // Lọc theo mục tiêu điều trị
            if (!empty($args['concerns'])) {
                $query->where(function ($q) use ($args) {
                    foreach ($args['concerns'] as $concern) {
                        $q->orWhere('description', 'LIKE', "%{$concern}%");
                    }
                });
            }

            // Lọc theo thời gian
            if (isset($args['duration_preference'])) {
                $query->where('duration', '<=', $args['duration_preference']);
            }

            // Lọc theo khoảng giá
            if (isset($args['price_range'])) {
                if (isset($args['price_range']['min'])) {
                    $query->where('single_price', '>=', $args['price_range']['min']);
                }
                if (isset($args['price_range']['max'])) {
                    $query->where('single_price', '<=', $args['price_range']['max']);
                }
            }

            // Lọc theo danh mục
            if (isset($args['category_id'])) {
                $query->where('category_id', $args['category_id']);
            }

            $services = $query->get()->map(function ($service) {
                return [
                    'id' => $service->id,
                    'name' => $service->service_name,
                    'description' => $service->description,
                    'duration' => $service->duration,
                    'single_price' => $service->single_price,
                    'combo_prices' => [
                        '5_sessions' => $service->combo_5_price,
                        '10_sessions' => $service->combo_10_price
                    ],
                    'category' => $service->category->name,
                    'images' => $service->media->pluck('url'),
                    'rating' => $service->ratings()->avg('rating')
                ];
            });

            return [
                'success' => true,
                'data' => $services,
                'count' => $services->count()
            ];
        } catch (\Exception $e) {
            Log::error("Service recommendations error: {$e->getMessage()}");
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    protected function handleProductDetails($args)
    {
        try {
            $product = Product::with(['category', 'media', 'attributes', 'ratings'])
                ->findOrFail($args['product_id']);

            return [
                'success' => true,
                'data' => [
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
                        'average' => $product->ratings()->avg('rating'),
                        'count' => $product->ratings()->count()
                    ]
                ]
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
            $service = Service::with(['category', 'media', 'ratings'])
                ->findOrFail($args['service_id']);

            return [
                'success' => true,
                'data' => [
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
                        'average' => $service->ratings()->avg('rating'),
                        'count' => $service->ratings()->count()
                    ]
                ]
            ];
        } catch (\Exception $e) {
            Log::error("Service details error: {$e->getMessage()}");
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
} 