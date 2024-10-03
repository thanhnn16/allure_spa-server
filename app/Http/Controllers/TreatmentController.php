<?php

namespace App\Http\Controllers;

use App\Models\Treatment;
use App\Models\TreatmentCategory;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;

class TreatmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Treatment::with(['category.parent']);

        if ($request->filled('search')) {
            $query->where('treatment_name', 'like', '%' . $request->input('search') . '%');
        }

        if ($request->filled('category')) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('id', $request->input('category'))
                  ->orWhere('parent_id', $request->input('category'));
            });
        }

        if ($request->filled('sort')) {
            $query->orderBy($request->input('sort'), $request->input('direction', 'asc'));
        }

        $treatments = $query->paginate($request->input('per_page', 10));

        // Thêm dòng này để kiểm tra dữ liệu
        Log::info($treatments);

        $categories = TreatmentCategory::with('children')->whereNull('parent_id')->get();

        return Inertia::render('Treatments/TreatmentView', [
            'treatments' => $treatments,
            'categories' => $categories,
            'filters' => $request->all(['search', 'category', 'sort', 'direction', 'per_page']),
        ]);
    }

    //reduce-fat
    public function reduceFat()
    {
        //
    }

    //massage
    public function massage()
    {
        //
    }

    //facial
    public function facial()
    {
        //
    }

    //hair-removal
    public function hairRemoval()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Treatment $treatment)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Treatment $treatment)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Treatment $treatment)
    {
        //
    }
}
