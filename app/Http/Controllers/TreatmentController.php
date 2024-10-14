<?php

namespace App\Http\Controllers;

use App\Services\TreatmentService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Treatment;

class TreatmentController extends BaseController
{
    protected $treatmentService;

    public function __construct(TreatmentService $treatmentService)
    {
        $this->treatmentService = $treatmentService;
    }

    public function index(Request $request)
    {
        $treatments = $this->treatmentService->getPaginatedTreatments($request->input('per_page', 15));

        if ($request->expectsJson()) {
            return $this->respondWithJson($treatments, 'Treatments retrieved successfully');
        }

        return $this->respondWithInertia('Treatments/Index', [
            'treatments' => $treatments
        ]);
    }

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

    // Implement other methods (store, update, show, destroy) similarly...
}
