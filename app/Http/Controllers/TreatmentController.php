<?php

namespace App\Http\Controllers;

use App\Services\TreatmentService;
use Illuminate\Http\Request;
use Inertia\Inertia;

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

    // Implement other methods (store, update, show, destroy) similarly...
}
