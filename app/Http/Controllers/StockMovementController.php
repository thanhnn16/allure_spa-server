<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StockMovement;
use App\Models\Product;
use App\Services\StockMovementService;
use Illuminate\Support\Facades\Auth;
use Inertia\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

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
    public function store(Request $request)
    {
        try {
            // Validate request
            $validated = $request->validate([
                'product_id' => 'required|exists:products,id',
                'quantity' => 'required|numeric|min:1',
                'type' => 'required|in:in,out',
                'reason' => 'nullable|string',
                'reference_number' => 'nullable|string',
                'note' => 'nullable|string'
            ]);

            // Process stock movement
            $stockMovement = DB::transaction(function () use ($validated) {
                $product = Product::findOrFail($validated['product_id']);

                $structuredNote = $this->prepareStructuredNote($validated);

                return $this->stockMovementService->createMovement(
                    $product,
                    $validated['quantity'],
                    $validated['type'],
                    json_encode($structuredNote)
                );
            });

            // Check if request is from web or api
            if ($request->wantsJson()) {
                // API Response
                return response()->json([
                    'message' => 'Stock movement created successfully',
                    'status_code' => 201,
                    'success' => true,
                    'data' => $stockMovement
                ], 201);
            }

            // Web Response (Inertia)
            return redirect()->back()->with('success', 'Tạo phiếu kho thành công');

        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'message' => $e->getMessage(),
                    'status_code' => 400,
                    'success' => false
                ], 400);
            }

            return redirect()->back()->withErrors(['message' => $e->getMessage()]);
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
