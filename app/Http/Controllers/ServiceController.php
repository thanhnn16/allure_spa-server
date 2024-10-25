<?php

namespace App\Http\Controllers;

use App\Services\ServiceService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Service;
use Illuminate\Support\Facades\Log;

/**
 * @OA\Tag(
 *     name="Services",
 *     description="API Endpoints của Service"
 * )
 */
class ServiceController extends BaseController
{
    protected $serviceService;

    public function __construct(ServiceService $serviceService)
    {
        $this->serviceService = $serviceService;
    }

    /**
     * @OA\Get(
     *     path="/api/services",
     *     summary="Lấy danh sách dịch vụ",
     *     tags={"Services"},
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
     *                     @OA\Items(ref="#/components/schemas/Service")
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
        $query = $request->get('query');
        
        $services = Service::where('service_name', 'LIKE', "%{$query}%")
            ->take(10)
            ->get(['id', 'service_name as name', 'single_price as price']);

        return response()->json([
            'data' => $services
        ]);
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
     *                 ref="#/components/schemas/Service"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Service not found"
     *     )
     * )
     */
    public function show(Request $request, $id)
    {
        $service = $this->serviceService->getServiceById($id);

        if (!$service) {
            if ($request->expectsJson()) {
                return $this->respondWithJson(null, 'Service not found', 404);
            }
            return redirect()->route('services.index')->with('error', 'Service not found');
        }

        if ($request->expectsJson()) {
            return $this->respondWithJson($service, 'Service retrieved successfully');
        }

        return $this->respondWithInertia('Services/Show', [
            'service' => $service->toArray()
        ]);
    }

    public function edit(Request $request, $id)
    {
        $service = $this->serviceService->getServiceById($id);

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
     * @OA\Get(
     *     path="/api/service-combos/{id}",
     *     summary="Lấy chi tiết một combo dịch vụ",
     *     tags={"Services"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID của combo dịch vụ",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Service combo retrieved successfully"),
     *             @OA\Property(property="status_code", type="integer", example=200),
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 ref="#/components/schemas/ServiceCombo"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Service combo not found"
     *     )
     * )
     */
    public function showServiceCombo(Request $request, $id)
    {
        $serviceCombo = $this->serviceService->getServiceComboById($id);

        if (!$serviceCombo) {
            return $this->respondWithJson(null, 'Service combo not found', 404);
        }

        return $this->respondWithJson($serviceCombo, 'Service combo retrieved successfully');
    }
}
