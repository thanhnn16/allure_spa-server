<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StockMovement;
use App\Models\Product;
use App\Services\StockMovementService;
use Illuminate\Support\Facades\Auth;
use Inertia\Response;
use Illuminate\Http\JsonResponse;

class StockMovementController extends BaseController
{
    protected StockMovementService $stockMovementService;

    public function __construct(StockMovementService $stockMovementService)
    {
        $this->stockMovementService = $stockMovementService;
    }

    /**
     * Display a listing of stock movements
     */
    public function index(Request $request): Response|JsonResponse
    {
        $movements = StockMovement::with(['product'])
            ->when($request->product_id, fn($q) => $q->where('product_id', $request->product_id))
            ->when($request->type, fn($q) => $q->where('type', $request->type))
            ->latest()
            ->paginate(15);

        $products = Product::select(['id', 'name', 'quantity', 'price'])
            ->orderBy('name')
            ->get();

        if ($request->wantsJson()) {
            return $this->respondWithJson($movements);
        }

        return $this->respondWithInertia('StockMovements/StockMovementView', [
            'stockMovements' => $movements,
            'products' => $products,
            'filters' => $request->only(['product_id', 'type'])
        ]);
    }

    /**
     * Store a newly created stock movement
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'type' => 'required|in:' . StockMovement::TYPE_IN . ',' . StockMovement::TYPE_OUT,
            'note' => 'nullable|string|max:1000',
            'reason' => 'nullable|string|max:255',
            'reference_number' => 'nullable|string|max:255'
        ]);

        try {
            $product = Product::findOrFail($validated['product_id']);

            $structuredNote = $this->prepareStructuredNote($validated);

            $movement = $this->stockMovementService->createMovement(
                $product,
                $validated['quantity'],
                $validated['type'],
                json_encode($structuredNote)
            );

            return $this->respondWithJson(
                $movement,
                'Stock movement created successfully',
                201
            );
        } catch (\Exception $e) {
            return $this->respondWithJson(
                null,
                $e->getMessage(),
                400
            );
        }
    }

    /**
     * Prepare structured note for stock movement
     */
    private function prepareStructuredNote(array $validated): array
    {
        return [
            'user' => Auth::user()->full_name,
            'reason' => $validated['reason'] ?? null,
            'reference' => $validated['reference_number'] ?? null,
            'comment' => $validated['note'] ?? null
        ];
    }
}
