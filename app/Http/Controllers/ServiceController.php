<?php

namespace App\Http\Controllers;

use App\Services\ServiceService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Services\MediaService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

/**
 * @OA\Tag(
 *     name="Services",
 *     description="API Endpoints của Service"
 * )
 */
class ServiceController extends BaseController
{
    protected $serviceService;
    protected $mediaService;

    public function __construct(ServiceService $serviceService, MediaService $mediaService)
    {
        $this->serviceService = $serviceService;
        $this->mediaService = $mediaService;
    }

    /**
     * @OA\Get(
     *     path="/api/services",
     *     summary="Lấy danh sách dịch vụ",
     *     tags={"Services"},
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Tìm kiếm theo tên dịch vụ",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="category",
     *         in="query",
     *         description="Lọc theo ID danh mục",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="sort",
     *         in="query",
     *         description="Sắp xếp theo trường",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="direction",
     *         in="query",
     *         description="Hướng sắp xếp (asc hoặc desc)",
     *         required=false,
     *         @OA\Schema(type="string", enum={"asc", "desc"})
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Số lượng item trên mỗi trang",
     *         required=false,
     *         @OA\Schema(type="integer", default=10)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Services retrieved successfully"),
     *             @OA\Property(property="status_code", type="integer", example=200),
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(
     *                     property="data",
     *                     type="array",
     *                     @OA\Items(
     *                         allOf={
     *                             @OA\Schema(ref="#/components/schemas/Service"),
     *                             @OA\Schema(
     *                                 type="object",
     *                                 @OA\Property(
     *                                     property="media",
     *                                     type="array",
     *                                     @OA\Items(
     *                                         type="object",
     *                                         @OA\Property(property="id", type="integer", example=1),
     *                                         @OA\Property(property="file_name", type="string", example="service1.jpg"),
     *                                         @OA\Property(property="file_path", type="string", example="uploads/services/service1.jpg"),
     *                                         @OA\Property(property="file_type", type="string", example="image/jpeg"),
     *                                         @OA\Property(property="file_size", type="integer", example=1024),
     *                                         @OA\Property(property="mediable_type", type="string", example="App\\Models\\Service"),
     *                                         @OA\Property(property="mediable_id", type="integer", example=1),
     *                                         @OA\Property(property="created_at", type="string", format="date-time"),
     *                                         @OA\Property(property="updated_at", type="string", format="date-time")
     *                                     )
     *                                 )
     *                             )
     *                         }
     *                     )
     *                 ),
     *                 @OA\Property(property="first_page_url", type="string", example="http://example.com/api/services?page=1"),
     *                 @OA\Property(property="from", type="integer", example=1),
     *                 @OA\Property(property="last_page", type="integer", example=5),
     *                 @OA\Property(property="last_page_url", type="string", example="http://example.com/api/services?page=5"),
     *                 @OA\Property(property="next_page_url", type="string", example="http://example.com/api/services?page=2"),
     *                 @OA\Property(property="path", type="string", example="http://example.com/api/services"),
     *                 @OA\Property(property="per_page", type="integer", example=10),
     *                 @OA\Property(property="prev_page_url", type="string", example=null),
     *                 @OA\Property(property="to", type="integer", example=10),
     *                 @OA\Property(property="total", type="integer", example=50)
     *             )
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $filters = $request->only(['search', 'category', 'sort', 'direction']);
        $services = $this->serviceService->getPaginatedServices($filters, $request->input('per_page', 10));

        if ($request->expectsJson()) {
            return $this->respondWithJson($services, 'Services retrieved successfully');
        }

        return $this->respondWithInertia('Services/ServiceView', [
            'services' => $services,
            'categories' => $this->serviceService->getAllCategories(),
            'filters' => $filters + ['per_page' => $request->input('per_page', 10)]
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/services/categories",
     *     summary="Lấy danh sách các danh mục service",
     *     tags={"Services"},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Service categories retrieved successfully"),
     *             @OA\Property(property="status_code", type="integer", example=200),
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="Massage"),
     *                     @OA\Property(property="parent_id", type="integer", example=null),
     *                     @OA\Property(property="image_id", type="integer", example=1),
     *                     @OA\Property(
     *                         property="children",
     *                         type="array",
     *                         @OA\Items(
     *                             type="object",
     *                             @OA\Property(property="id", type="integer", example=2),
     *                             @OA\Property(property="name", type="string", example="Massage chân"),
     *                             @OA\Property(property="parent_id", type="integer", example=1),
     *                             @OA\Property(property="image_id", type="integer", example=2)
     *                         )
     *                     ),
     *                     @OA\Property(
     *                         property="services",
     *                         type="array",
     *                         @OA\Items(
     *                             type="object",
     *                             @OA\Property(property="id", type="integer", example=1),
     *                             @OA\Property(property="name", type="string", example="Massage cổ vai gáy"),
     *                             @OA\Property(property="price", type="number", example=500000),
     *                             @OA\Property(property="duration", type="integer", example=60),
     *                             @OA\Property(property="category_id", type="integer", example=1)
     *                         )
     *                     ),
     *                     @OA\Property(
     *                         property="image",
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="url", type="string", example="http://example.com/images/massage.jpg")
     *                     ),
     *                     @OA\Property(
     *                         property="translations",
     *                         type="array",
     *                         @OA\Items(
     *                             type="object",
     *                             @OA\Property(property="id", type="integer", example=1),
     *                             @OA\Property(property="service_category_id", type="integer", example=1),
     *                             @OA\Property(property="locale", type="string", example="en"),
     *                             @OA\Property(property="name", type="string", example="Massage")
     *                         )
     *                     )
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function categories(Request $request)
    {
        $categories = $this->serviceService->getAllCategories();

        if ($request->expectsJson()) {
            return $this->respondWithJson($categories, 'Service categories retrieved successfully');
        }

        return $this->respondWithInertia('Services/Categories', [
            'categories' => $categories
        ]);
    }

    public function searchServices(Request $request)
    {
        try {
            $query = $request->get('query');

            Log::info('Service search query:', ['query' => $query]);

            $searchTerm = mb_strtolower($query);

            $services = Service::where(function ($q) use ($searchTerm) {
                $q->whereRaw('LOWER(service_name) LIKE ?', ['%' . $searchTerm . '%']);
            })
                ->select([
                    'id',
                    'service_name',
                    'single_price',
                    'combo_5_price',
                    'combo_10_price'
                ])
                ->get();

            Log::info('Services found:', [
                'count' => $services->count(),
                'services' => $services->toArray()
            ]);

            $transformedServices = $services->map(function ($service) {
                return [
                    'id' => $service->id,
                    'name' => $service->service_name,
                    'service_name' => $service->service_name,
                    'single_price' => (float)($service->single_price ?? 0),
                    'combo_5_price' => (float)($service->combo_5_price ?? 0),
                    'combo_10_price' => (float)($service->combo_10_price ?? 0),
                    'price' => (float)($service->single_price ?? 0), // Default to single_price
                    'item_type' => 'service'
                ];
            });

            return response()->json(['data' => $transformedServices]);
        } catch (\Exception $e) {
            Log::error('Service search error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Internal server error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/services/{id}",
     *     summary="Lấy chi tiết một service",
     *     tags={"Services"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID của service",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Service retrieved successfully"),
     *             @OA\Property(property="status_code", type="integer", example=200),
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="service_name", type="string", example="Chăm da Amino - Phù hợp mọi loại da"),
     *                 @OA\Property(property="description", type="string", example="Liệu trình chăm sóc da mặt phù hợp cho mọi loại da"),
     *                 @OA\Property(property="duration", type="integer", example=60),
     *                 @OA\Property(property="category_id", type="integer", example=1),
     *                 @OA\Property(property="single_price", type="number", format="float", example=1350000),
     *                 @OA\Property(property="combo_5_price", type="number", format="float", example=5400000),
     *                 @OA\Property(property="combo_10_price", type="number", format="float", example=9450000),
     *                 @OA\Property(property="validity_period", type="integer", example=365),
     *                 @OA\Property(property="created_at", type="string", format="date-time", nullable=true),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", nullable=true),
     *                 @OA\Property(property="deleted_at", type="string", format="date-time", nullable=true),
     *                 @OA\Property(
     *                     property="category",
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="service_category_name", type="string", example="Chăm sóc da mặt"),
     *                     @OA\Property(property="parent_id", type="integer", nullable=true),
     *                     @OA\Property(property="created_at", type="string", format="date-time", nullable=true),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", nullable=true),
     *                     @OA\Property(property="deleted_at", type="string", format="date-time", nullable=true)
     *                 ),
     *                 @OA\Property(property="media", type="array", @OA\Items(type="object")),
     *                 @OA\Property(property="price_history", type="array", @OA\Items(type="object"))
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Service not found"
     *     )
     * )
     */
    public function show(Request $request, Service $service)
    {
        $userId = $request->query('user_id');
        $service = $this->serviceService->getServiceById($service->id, $userId);

        if (!$service) {
            if ($request->expectsJson()) {
                return $this->respondWithJson(null, 'Service not found', 404);
            }
            return redirect()->route('services.index')->with('error', 'Service not found');
        }

        if ($request->expectsJson()) {
            return $this->respondWithJson($service, 'Service retrieved successfully');
        }

        $categories = ServiceCategory::all();

        return $this->respondWithInertia('Services/Show', [
            'service' => $service->toArray(),
            'categories' => $categories
        ]);
    }

    public function edit(Request $request, Service $service)
    {
        $service = $this->serviceService->getServiceById($service->id);

        if (!$service) {
            return redirect()->route('services.index')->with('error', 'Service not found');
        }

        return $this->respondWithInertia('Services/Edit', [
            'service' => $service
        ]);
    }

    public function update(Request $request, $id)
    {
        $service = $this->serviceService->updateService($id, $request->all());

        if (!$service) {
            return redirect()->route('services.index')->with('error', 'Service not found');
        }

        return redirect()->route('services.show', $id)->with('success', 'Service updated successfully');
    }

    public function destroy(Request $request, $id)
    {
        $result = $this->serviceService->deleteService($id);

        if (!$result) {
            return redirect()->route('services.index')->with('error', 'Service not found');
        }

        return redirect()->route('services.index')->with('success', 'Service deleted successfully');
    }

    /**
     * Fetch services for appointment booking
     */
    public function getServicesForAppointment(Request $request)
    {
        try {
            $services = Service::with('category')
                ->select([
                    'id',
                    'service_name',
                    'single_price as price',
                    'duration',
                    'category_id'
                ])
                ->get()
                ->map(function ($service) {
                    return [
                        'id' => $service->id,
                        'name' => $service->service_name,
                        'price' => $service->price,
                        'duration' => $service->duration,
                        'category_name' => $service->category ? $service->category->service_category_name : null
                    ];
                });

            return $this->respondWithJson($services, 'Services retrieved successfully');
        } catch (\Exception $e) {
            Log::error('Error fetching services for appointment: ' . $e->getMessage());
            return $this->respondWithError('Error fetching services: ' . $e->getMessage());
        }
    }

    public function getTranslations(Service $service)
    {
        try {
            $translations = $service->translations()
                ->select('language', 'field', 'value')
                ->get()
                ->groupBy('language')
                ->map(function ($items) {
                    return $items->mapWithKeys(function ($item) {
                        return [$item->field => $item->value];
                    });
                });

            return $this->respondWithJson($translations, 'Translations retrieved successfully');
        } catch (\Exception $e) {
            return $this->respondWithError('Error fetching translations: ' . $e->getMessage());
        }
    }

    public function updateTranslations(Request $request, Service $service)
    {
        try {
            DB::beginTransaction();

            foreach ($request->translations as $language => $fields) {
                foreach ($fields as $field => $value) {
                    $service->translations()->updateOrCreate(
                        [
                            'language' => $language,
                            'field' => $field,
                        ],
                        ['value' => $value]
                    );
                }
            }

            DB::commit();
            return $this->respondWithJson(null, 'Translations updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->respondWithError('Error updating translations: ' . $e->getMessage());
        }
    }

    /**
     * @OA\Post(
     *     path="/api/services",
     *     summary="Tạo dịch vụ mới",
     *     tags={"Services"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"service_name","category_id","single_price","duration"},
     *             @OA\Property(property="service_name", type="string", example="Massage chân"),
     *             @OA\Property(property="category_id", type="integer", example=1),
     *             @OA\Property(property="single_price", type="number", example=500000),
     *             @OA\Property(property="combo_5_price", type="number", example=2000000),
     *             @OA\Property(property="combo_10_price", type="number", example=3500000),
     *             @OA\Property(property="duration", type="integer", example=60),
     *             @OA\Property(property="description", type="string"),
     *             @OA\Property(property="validity_period", type="integer", example=365)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Tạo dịch vụ thành công"
     *     )
     * )
     */
    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            // Validate request data
            $validatedData = $request->validate([
                'service_name' => 'required|string|max:255',
                'category_id' => 'required|exists:service_categories,id',
                'single_price' => 'required|numeric|min:0',
                'combo_5_price' => 'nullable|numeric|min:0',
                'combo_10_price' => 'nullable|numeric|min:0',
                'duration' => 'required|integer|min:0',
                'description' => 'nullable|string',
                'validity_period' => 'nullable|integer|min:0'
            ]);

            // Create service
            $service = $this->serviceService->createService($validatedData);

            // Handle images if present
            if ($request->hasFile('images')) {
                $this->mediaService->createMultiple($service, $request->file('images'), 'image');
            }

            DB::commit();

            if ($request->expectsJson()) {
                return $this->respondWithJson($service, 'Tạo dịch vụ thành công', 201);
            }

            return redirect()->route('services.index')
                ->with('success', 'Dịch vụ đã được tạo thành công.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Service creation failed:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            if ($request->expectsJson()) {
                return $this->respondWithError('Không thể tạo dịch vụ: ' . $e->getMessage(), 500);
            }

            return back()->withErrors(['error' => 'Không thể tạo dịch vụ'])->withInput();
        }
    }

    public function uploadImages(Request $request, Service $service)
    {
        try {
            Log::channel('service_debug')->info('Image upload started for service:', [
                'service_id' => $service->id,
                'has_files' => $request->hasFile('images'),
                'files_count' => $request->hasFile('images') ? count($request->file('images')) : 0
            ]);

            if (!$request->hasFile('images')) {
                return $this->respondWithError('Không có ảnh được tải lên', 400);
            }

            $mediaItems = $this->mediaService->createMultiple($service, $request->file('images'), 'image');

            Log::channel('service_debug')->info('Images uploaded successfully:', [
                'media_items_count' => count($mediaItems),
                'media_items' => collect($mediaItems)->map(fn($item) => [
                    'id' => $item->id,
                    'path' => $item->file_path,
                    'full_url' => $item->getFullUrlAttribute()
                ])
            ]);

            return $this->respondWithJson([
                'media' => $mediaItems,
                'message' => 'Tải ảnh lên thành công'
            ]);
        } catch (\Exception $e) {
            Log::channel('service_debug')->error('Image upload failed:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return $this->respondWithError('Không thể tải ảnh lên: ' . $e->getMessage(), 500);
        }
    }
}
