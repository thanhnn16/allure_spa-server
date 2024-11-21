<?php

namespace App\Http\Controllers;

use App\Enums\AuthErrorCode;
use App\Services\VoucherService;
use Illuminate\Http\Request;
use App\Models\Voucher;
use App\Models\UserVoucher;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use OpenApi\Annotations as OA;

class VoucherController extends BaseController
{
    protected $voucherService;

    public function __construct(VoucherService $voucherService)
    {
        $this->voucherService = $voucherService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $vouchers = Voucher::where('status', 'active')
                ->where('end_date', '>', now())
                ->where(function ($query) {
                    $query->where('is_unlimited', true)
                        ->orWhere(function ($q) {
                            $q->where('is_unlimited', false)
                                ->whereRaw('used_times < usage_limit');
                        });
                })
                ->orderBy('created_at', 'desc')
                ->get();

            Log::info('Fetched vouchers:', ['count' => $vouchers->count(), 'vouchers' => $vouchers]);

            if ($request->wantsJson()) {
                return $this->respondWithJson($vouchers);
            }

            return $this->respondWithInertia('Vouchers/Index', [
                'vouchers' => $vouchers,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching vouchers:', ['error' => $e->getMessage()]);

            if ($request->wantsJson()) {
                return $this->respondWithError($e->getMessage());
            }

            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'code' => 'required|string|unique:vouchers,code',
                'description' => 'nullable|string',
                'discount_type' => 'required|in:percentage,fixed',
                'discount_value' => 'required|numeric|min:0',
                'min_order_value' => 'required|numeric|min:0',
                'max_discount_amount' => 'required|numeric|min:0',
                'usage_limit' => 'required|integer|min:1',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after:start_date',
                'is_unlimited' => 'required|boolean',
                'uses_per_user' => 'required|integer|min:1',
                'status' => 'required|in:active,inactive'
            ]);

            $voucher = Voucher::create([
                ...$validated,
                'used_times' => 0
            ]);

            return response()->json([
                'message' => 'Voucher đã được tạo thành công',
                'data' => $voucher
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    /**
     * Assign voucher to user
     */
    public function assignToUser(Request $request)
    {
        try {
            $validated = $request->validate([
                'user_id' => 'required|string',
                'voucher_id' => 'required|integer',
                'total_uses' => 'required|integer|min:1'
            ]);

            $result = $this->voucherService->assignToUser(
                $validated['user_id'],
                $validated['voucher_id'],
                $validated['total_uses']
            );

            return response()->json([
                'message' => 'Voucher đã được gán cho người dùng thành công',
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/vouchers/my-vouchers",
     *     summary="Get authenticated user's vouchers",
     *     description="Retrieve all vouchers belonging to the authenticated user",
     *     operationId="getMyVouchers",
     *     tags={"Vouchers"},
     *     security={{ "sanctum": {} }},
     *     
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example=""),
     *             @OA\Property(property="status_code", type="integer", example=200),
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="code", type="string", example="SUMMER2024"),
     *                     @OA\Property(property="description", type="string", example="Summer sale voucher"),
     *                     @OA\Property(property="discount_type", type="string", enum={"percentage", "fixed"}, example="percentage"),
     *                     @OA\Property(property="discount_value", type="number", format="float", example=10),
     *                     @OA\Property(property="min_order_value", type="number", format="float", example=100000),
     *                     @OA\Property(property="max_discount_amount", type="number", format="float", example=50000),
     *                     @OA\Property(property="formatted_discount", type="string", example="10%"),
     *                     @OA\Property(property="min_order_value_formatted", type="string", example="100.000 ₫"),
     *                     @OA\Property(property="max_discount_amount_formatted", type="string", example="50.000 ₫"),
     *                     @OA\Property(property="start_date", type="string", format="date", example="2024-01-01"),
     *                     @OA\Property(property="end_date", type="string", format="date", example="2024-12-31"),
     *                     @OA\Property(property="start_date_formatted", type="string", example="01/01/2024"),
     *                     @OA\Property(property="end_date_formatted", type="string", example="31/12/2024"),
     *                     @OA\Property(property="is_active", type="boolean", example=true),
     *                     @OA\Property(property="remaining_uses", type="integer", example=5),
     *                     @OA\Property(property="total_uses", type="integer", example=10)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated."),
     *             @OA\Property(property="status_code", type="integer", example=401),
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="data", type="null", example=null)
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Error message"),
     *             @OA\Property(property="status_code", type="integer", example=400),
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="data", type="null", example=null)
     *         )
     *     )
     * )
     */
    public function getMyVouchers()
    {
        try {
            $userId = Auth::id();
            $vouchers = UserVoucher::with(['voucher' => function ($query) {
                $query->select([
                    'id',
                    'code',
                    'description',
                    'discount_type',
                    'discount_value',
                    'min_order_value',
                    'max_discount_amount',
                    'start_date',
                    'end_date',
                    'status',
                    'is_unlimited',
                    'usage_limit',
                    'used_times'
                ]);
            }])
                ->where('user_id', $userId)
                ->get()
                ->map(function ($userVoucher) {
                    $voucher = $userVoucher->voucher;
                    return [
                        'id' => $voucher->id,
                        'code' => $voucher->code,
                        'description' => $voucher->description,
                        'discount_type' => $voucher->discount_type,
                        'discount_value' => $voucher->discount_value,
                        'min_order_value' => $voucher->min_order_value,
                        'max_discount_amount' => $voucher->max_discount_amount,
                        'formatted_discount' => $voucher->formatted_discount,
                        'min_order_value_formatted' => $voucher->min_order_value_formatted,
                        'max_discount_amount_formatted' => $voucher->max_discount_amount_formatted,
                        'start_date' => $voucher->start_date,
                        'end_date' => $voucher->end_date,
                        'start_date_formatted' => $voucher->start_date_formatted,
                        'end_date_formatted' => $voucher->end_date_formatted,
                        'is_active' => $voucher->is_active,
                        'remaining_uses' => $userVoucher->remaining_uses,
                        'total_uses' => $userVoucher->total_uses,
                    ];
                });

            return $this->respondWithJson($vouchers);
        } catch (\Exception $e) {
            return $this->respondWithError(AuthErrorCode::SERVER_ERROR->value);
        }
    }

    /**
     * Get vouchers for a specific user
     * 
     * @param string $userId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUserVouchers(string $userId)
    {
        try {
            $vouchers = UserVoucher::with(['voucher' => function ($query) {
                $query->select([
                    'id',
                    'code',
                    'description',
                    'discount_type',
                    'discount_value',
                    'min_order_value',
                    'max_discount_amount',
                    'start_date',
                    'end_date',
                    'status',
                    'is_unlimited',
                    'usage_limit',
                    'used_times'
                ]);
            }])
                ->where('user_id', $userId)
                ->get()
                ->map(function ($userVoucher) {
                    $voucher = $userVoucher->voucher;
                    return [
                        'id' => $voucher->id,
                        'code' => $voucher->code,
                        'description' => $voucher->description,
                        'discount_type' => $voucher->discount_type,
                        'discount_value' => $voucher->discount_value,
                        'min_order_value' => $voucher->min_order_value,
                        'max_discount_amount' => $voucher->max_discount_amount,
                        'formatted_discount' => $voucher->formatted_discount,
                        'min_order_value_formatted' => $voucher->min_order_value_formatted,
                        'max_discount_amount_formatted' => $voucher->max_discount_amount_formatted,
                        'start_date' => $voucher->start_date,
                        'end_date' => $voucher->end_date,
                        'start_date_formatted' => $voucher->start_date_formatted,
                        'end_date_formatted' => $voucher->end_date_formatted,
                        'is_active' => $voucher->is_active,
                        'remaining_uses' => $userVoucher->remaining_uses,
                        'total_uses' => $userVoucher->total_uses,
                    ];
                });

            return response()->json([
                'data' => $vouchers
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Return voucher from user
     */
    public function returnVoucher(Request $request)
    {
        try {
            $validated = $request->validate([
                'user_id' => 'required|string',
                'voucher_id' => 'required|integer'
            ]);

            $userVoucher = UserVoucher::where('user_id', $validated['user_id'])
                ->where('voucher_id', $validated['voucher_id'])
                ->first();

            if (!$userVoucher) {
                throw new \Exception('Không tìm thấy voucher');
            }

            // Delete the user voucher
            $userVoucher->delete();

            return response()->json([
                'message' => 'Đã trả lại voucher thành công'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Toggle voucher status
     */
    public function toggleStatus(string $id)
    {
        try {
            $voucher = Voucher::findOrFail($id);
            $voucher->status = $voucher->status === 'active' ? 'inactive' : 'active';
            $voucher->save();

            return response()->json([
                'success' => true,
                'message' => 'Đã cập nhật trạng thái voucher',
                'data' => $voucher
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
}
