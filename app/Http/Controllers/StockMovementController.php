<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StockMovement;
use App\Models\Product;
use App\Services\StockMovementService;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class StockMovementController extends Controller
{
    protected $stockMovementService;

    public function __construct(StockMovementService $stockMovementService)
    {
        $this->stockMovementService = $stockMovementService;
    }

    public function index(Request $request)
    {
        $movements = StockMovement::with(['product'])
            ->when($request->product_id, function ($q) use ($request) {
                return $q->where('product_id', $request->product_id);
            })
            ->when($request->type, function ($q) use ($request) {
                return $q->where('type', $request->type);
            })
            ->latest()
            ->paginate(15);

        $products = Product::select(['id', 'name', 'quantity'])
            ->orderBy('name')
            ->get();

        if ($request->wantsJson()) {
            return response()->json($movements);
        }

        return Inertia::render('StockMovements/StockMovementView', [
            'stockMovements' => $movements,
            'products' => $products,
            'filters' => $request->only(['product_id', 'type'])
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'type' => 'required|in:in,out',
            'note' => 'nullable|string|max:1000',
            'reason' => 'nullable|string|max:255',
            'reference_number' => 'nullable|string|max:255'
        ]);

        try {
            $product = Product::findOrFail($validated['product_id']);

            // Tạo structured note
            $structuredNote = [
                'user' => Auth::user()->full_name,
                'reason' => $validated['reason'],
                'reference' => $validated['reference_number'],
                'comment' => $validated['note']
            ];

            $movement = $this->stockMovementService->createMovement(
                $product,
                $validated['quantity'],
                $validated['type'],
                json_encode($structuredNote)
            );

            return response()->json([
                'message' => 'Stock movement created successfully',
                'data' => $movement
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 400);
        }
    }
}
