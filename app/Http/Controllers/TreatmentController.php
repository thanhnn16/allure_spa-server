<?php

namespace App\Http\Controllers;

use App\Services\TreatmentService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Treatment;

/**
 * @OA\Tag(
 *     name="Treatments",
 *     description="API Endpoints của Treatment"
 * )
 */
class TreatmentController extends BaseController
{
    protected $treatmentService;

    public function __construct(TreatmentService $treatmentService)
    {
        $this->treatmentService = $treatmentService;
    }

    /**
     * @OA\Get(
     *     path="/api/treatments",
     *     summary="Lấy danh sách các treatment",
     *     tags={"Treatments"},
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
     *             @OA\Property(property="message", type="string", example="Treatments retrieved successfully"),
     *             @OA\Property(property="status_code", type="integer", example=200),
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="data", type="array", 
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="name", type="string", example="Massage cổ vai gáy"),
     *                         @OA\Property(property="price", type="number", example=500000),
     *                         @OA\Property(property="duration", type="integer", example=60),
     *                         @OA\Property(property="description", type="string", example="Massage giúp giảm đau nhức cổ vai gáy"),
     *                         @OA\Property(property="category_id", type="integer", example=1),
     *                         @OA\Property(
     *                             property="category",
     *                             type="object",
     *                             @OA\Property(property="id", type="integer", example=1),
     *                             @OA\Property(property="name", type="string", example="Massage")
     *                         )
     *                     )
     *                 ),
     *                 @OA\Property(property="first_page_url", type="string", example="http://example.com/api/treatments?page=1"),
     *                 @OA\Property(property="from", type="integer", example=1),
     *                 @OA\Property(property="last_page", type="integer", example=5),
     *                 @OA\Property(property="last_page_url", type="string", example="http://example.com/api/treatments?page=5"),
     *                 @OA\Property(property="next_page_url", type="string", example="http://example.com/api/treatments?page=2"),
     *                 @OA\Property(property="path", type="string", example="http://example.com/api/treatments"),
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
        $treatments = $this->treatmentService->getPaginatedTreatments($request->input('per_page', 10));

        if ($request->expectsJson()) {
            return $this->respondWithJson($treatments, 'Treatments retrieved successfully');
        }

        return $this->respondWithInertia('Treatments/Index', [
            'treatments' => $treatments
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/treatments/categories",
     *     summary="Lấy danh sách các danh mục treatment",
     *     tags={"Treatments"},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Treatment categories retrieved successfully"),
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
     *                         property="treatments",
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
     *                             @OA\Property(property="treatment_category_id", type="integer", example=1),
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
        $categories = $this->treatmentService->getAllCategories();

        if ($request->expectsJson()) {
            return $this->respondWithJson($categories, 'Treatment categories retrieved successfully');
        }

        return $this->respondWithInertia('Treatments/Categories', [
            'categories' => $categories
        ]);
    }

    public function searchTreatments(Request $request)
    {
        $query = $request->get('query');
        $treatments = Treatment::with('category')
            ->where('name', 'like', "%{$query}%")
            ->orWhere('description', 'like', "%{$query}%")
            ->select('id', 'name', 'price', 'duration', 'category_id')
            ->get();

        return response()->json(['data' => $treatments]);
    }

    /**
     * @OA\Get(
     *     path="/api/treatments/{id}",
     *     summary="Lấy chi tiết một treatment",
     *     tags={"Treatments"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID của treatment",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Treatment retrieved successfully"),
     *             @OA\Property(property="status_code", type="integer", example=200),
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Massage cổ vai gáy"),
     *                 @OA\Property(property="price", type="number", example=500000),
     *                 @OA\Property(property="duration", type="integer", example=60),
     *                 @OA\Property(property="description", type="string", example="Massage giúp giảm đau nhức cổ vai gáy"),
     *                 @OA\Property(property="category_id", type="integer", example=1),
     *                 @OA\Property(
     *                     property="category",
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="Massage")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Treatment not found"
     *     )
     * )
     */
    public function show(Request $request, $id)
    {
        $treatment = $this->treatmentService->getTreatmentById($id);

        if (!$treatment) {
            return $this->respondWithJson(null, 'Treatment not found', 404);
        }

        if ($request->expectsJson()) {
            return $this->respondWithJson($treatment, 'Treatment retrieved successfully');
        }

        return $this->respondWithInertia('Treatments/Show', [
            'treatment' => $treatment
        ]);
    }

    public function edit(Request $request, $id)
    {
        $treatment = $this->treatmentService->getTreatmentById($id);

        if (!$treatment) {
            return redirect()->route('treatments.index')->with('error', 'Treatment not found');
        }

        return $this->respondWithInertia('Treatments/Edit', [
            'treatment' => $treatment
        ]);
    }

    public function update(Request $request, $id)
    {
        $treatment = $this->treatmentService->updateTreatment($id, $request->all());

        if (!$treatment) {
            return redirect()->route('treatments.index')->with('error', 'Treatment not found');
        }

        return redirect()->route('treatments.show', $id)->with('success', 'Treatment updated successfully');
    }

    public function destroy(Request $request, $id)
    {
        $result = $this->treatmentService->deleteTreatment($id);

        if (!$result) {
            return redirect()->route('treatments.index')->with('error', 'Treatment not found');
        }

        return redirect()->route('treatments.index')->with('success', 'Treatment deleted successfully');
    }

    /**
     * @OA\Get(
     *     path="/api/treatment-combos/{id}",
     *     summary="Lấy chi tiết một liệu trình",
     *     tags={"Treatments"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID của liệu trình",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Treatment combo retrieved successfully"),
     *             @OA\Property(property="status_code", type="integer", example=200),
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Gói trị liệu toàn diện"),
     *                 @OA\Property(property="price", type="number", example=1500000),
     *                 @OA\Property(property="description", type="string", example="Gói trị liệu toàn diện bao gồm nhiều liệu pháp"),
     *                 @OA\Property(
     *                     property="treatments",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="name", type="string", example="Massage cổ vai gáy"),
     *                         @OA\Property(property="price", type="number", example=500000),
     *                         @OA\Property(property="duration", type="integer", example=60),
     *                         @OA\Property(property="pivot", type="object",
     *                             @OA\Property(property="quantity", type="integer", example=1)
     *                         )
     *                     )
     *                 ),
     *                 @OA\Property(
     *                     property="image",
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="url", type="string", example="http://example.com/images/treatment-combo.jpg")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Treatment combo not found"
     *     )
     * )
     */
    public function showTreatmentCombo(Request $request, $id)
    {
        $treatmentCombo = $this->treatmentService->getTreatmentComboById($id);

        if (!$treatmentCombo) {
            return $this->respondWithJson(null, 'Treatment combo not found', 404);
        }

        return $this->respondWithJson($treatmentCombo, 'Treatment combo retrieved successfully');
    }
}
