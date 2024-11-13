<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Invoice;
use App\Models\PaymentMethod;
use App\Models\Voucher;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

/**
 * @OA\Tag(
 *     name="Orders",
 *     description="API Endpoints quản lý đơn hàng"
 * )
 */

class OrderController extends BaseController
{
    /**
     * @OA\Schema(
     *     schema="OrderRequest",
     *     required={"user_id", "payment_method_id", "order_items", "total_amount", "discount_amount"},
     *     @OA\Property(property="user_id", type="string", format="uuid"),
     *     @OA\Property(property="payment_method_id", type="integer"),
     *     @OA\Property(property="voucher_id", type="integer", nullable=true),
     *     @OA\Property(property="total_amount", type="number", format="float"),
     *     @OA\Property(property="discount_amount", type="number", format="float"),
     *     @OA\Property(property="note", type="string", nullable=true),
     *     @OA\Property(
     *         property="order_items",
     *         type="array",
     *         @OA\Items(ref="#/components/schemas/OrderItemRequest")
     *     )
     * )
     */

    /**
     * @OA\Schema(
     *     schema="OrderItemRequest",
     *     required={"item_type", "item_id", "quantity", "price"},
     *     @OA\Property(property="item_type", type="string", enum={"product", "service"}),
     *     @OA\Property(property="item_id", type="integer"),
     *     @OA\Property(property="service_type", type="string", nullable=true, enum={"single", "combo_5", "combo_10"}),
     *     @OA\Property(property="quantity", type="integer", minimum=1),
     *     @OA\Property(property="price", type="number", format="float")
     * )
     */

    /**
     * @OA\Schema(
     *     schema="OrderResponse",
     *     @OA\Property(property="success", type="boolean"),
     *     @OA\Property(property="message", type="string"),
     *     @OA\Property(property="data", ref="#/components/schemas/Order")
     * )
     */

    

    /**
     * @OA\Get(
     *     path="/api/orders/{order}",
     *     operationId="getOrderById",
     *     summary="Xem chi tiết đơn hàng",
     *     tags={"Orders"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="order",
     *         in="path",
     *         required=true,
     *         description="Order ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/Order")
     *     )
     * )
     */

    /**
     * @OA\Post(
     *     path="/api/orders",
     *     summary="Create new order",
     *     description="Create a new order with items",
     *     tags={"Orders"},
     *     security={{ "sanctum": {} }},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"user_id", "payment_method_id", "order_items", "total_amount"},
     *             @OA\Property(property="user_id", type="string", format="uuid"),
     *             @OA\Property(property="payment_method_id", type="integer"),
     *             @OA\Property(property="voucher_id", type="integer", nullable=true),
     *             @OA\Property(property="total_amount", type="number", format="float"),
     *             @OA\Property(property="discount_amount", type="number", format="float"),
     *             @OA\Property(property="note", type="string", nullable=true),
     *             @OA\Property(
     *                 property="order_items",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="item_type", type="string", enum={"product", "service"}),
     *                     @OA\Property(property="item_id", type="integer"),
     *                     @OA\Property(property="service_type", type="string", nullable=true),
     *                     @OA\Property(property="quantity", type="integer", minimum=1),
     *                     @OA\Property(property="price", type="number", format="float")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Order created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Order created successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Order")
     *         )
     *     ),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */

    /**
     * @OA\Put(
     *     path="/api/orders/{order}",
     *     operationId="updateOrder",
     *     summary="Cập nhật đơn hàng",
     *     tags={"Orders"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="order",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string"),
     *             @OA\Property(property="note", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Order updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/OrderResponse")
     *     )
     * )
     */

    /**
     * @OA\Delete(
     *     path="/api/orders/{order}",
     *     summary="Xóa đơn hàng",
     *     tags={"Orders"},
     *     security={{ "sanctum": {} }},
     *     @OA\Parameter(
     *         name="order",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Xóa thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Đơn hàng đã được xóa thành công")
     *         )
     *     )
     * )
     */

    /**
     * @OA\Post(
     *     path="/api/orders/{order}/create-invoice",
     *     summary="Tạo hóa đơn từ đơn hàng",
     *     tags={"Orders"},
     *     security={{ "sanctum": {} }},
     *     @OA\Parameter(
     *         name="order",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="note", type="string", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Tạo hóa đơn thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", ref="#/components/schemas/Invoice")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Lỗi khi tạo hóa đơn"
     *     )
     * )
     */

    /**
     * @OA\Get(
     *     path="/api/orders/my-orders",
     *     summary="Get current user's orders",
     *     description="Retrieve list of orders for authenticated user",
     *     tags={"Orders"},
     *     security={{ "sanctum": {} }},
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter by order status",
     *         required=false,
     *         @OA\Schema(type="string", enum={"pending", "confirmed", "shipping", "delivered", "completed", "cancelled"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Orders retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/Order")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */

    /**
     * @OA\Put(
     *     path="/api/orders/{order}/update-status",
     *     summary="Update order status",
     *     description="Update status of an existing order",
     *     tags={"Orders"},
     *     security={{ "sanctum": {} }},
     *     @OA\Parameter(
     *         name="order",
     *         in="path",
     *         required=true,
     *         description="Order ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"status"},
     *             @OA\Property(property="status", type="string", enum={"cancelled"}),
     *             @OA\Property(property="cancel_reason", type="string", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Status updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", ref="#/components/schemas/Order")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=404, description="Order not found")
     * )
     */

    /**
     * @OA\Delete(
     *     path="/api/orders/{order}/cancel",
     *     summary="Hủy đơn hàng (dành cho khách hàng)",
     *     tags={"Orders"},
     *     security={{ "sanctum": {} }},
     *     @OA\Parameter(
     *         name="order",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="cancel_reason", type="string", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Hủy đơn hàng thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Không có quyền hủy đơn hàng này"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Không thể hủy đơn hàng ở trạng thái hiện tại"
     *     )
     * )
     */

    
    

    protected $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    /**
     * @OA\Get(
     *     path="/api/orders",
     *     operationId="getOrders",
     *     summary="Lấy danh sách đơn hàng",
     *     tags={"Orders"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Lọc theo trạng thái đơn hàng",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             enum={"pending", "confirmed", "shipping", "delivered", "completed", "cancelled"}
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Tìm kiếm theo tên khách hàng hoặc ID đơn hàng",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="current_page", type="integer"),
     *                 @OA\Property(
     *                     property="data",
     *                     type="array",
     *                     @OA\Items(ref="#/components/schemas/Order")
     *                 )
     *             )
     *         )
     *     )
     * )
     */

    public function index()
    {
        $orders = Order::with(['user', 'invoice'])
            ->when(request('status'), function ($query, $status) {
                return $query->where('status', $status);
            })
            ->when(request('search'), function ($query, $search) {
                return $query->whereHas('user', function ($q) use ($search) {
                    $q->where('full_name', 'like', "%{$search}%");
                })->orWhere('id', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(10);

        return Inertia::render('Order/OrderView', [
            'orders' => $orders
        ]);
    }

    public function show(Order $order)
    {
        $order->load([
            'user',
            'order_items.product',
            'order_items.service',
            'invoice'
        ]);
        return Inertia::render('Order/OrderShow', [
            'order' => $order
        ]);
    }

    public function edit(Order $order)
    {
        $order->load(['user', 'order_items']);
        return Inertia::render('Order/OrderEdit', [
            'order' => $order
        ]);
    }

   

    public function update(Request $request, Order $order)
    {
        try {
            $validated = $request->validate([
                'status' => 'required|in:pending,confirmed,shipping,delivered,completed,cancelled',
                'note' => 'nullable|string'
            ]);

            $order->update([
                'status' => $validated['status'],
                'note' => $validated['note']
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật trạng thái đơn hàng thành công',
                'data' => $order
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi cập nhật trạng thái đơn hàng',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Order $order)
    {
        $order->delete();
        return redirect()->route('orders.index')->with('success', 'Đơn hàng đã được xóa thành công.');
    }


    public function store(Request $request)
    {
        try {
            // Validate request
            $validatedData = $request->validate([
                'user_id' => 'required|exists:users,id',
                'payment_method_id' => 'required|exists:payment_methods,id',
                'voucher_id' => 'nullable|exists:vouchers,id',
                'order_items' => 'required|array|min:1',
                'order_items.*.item_type' => 'required|in:product,service',
                'order_items.*.item_id' => 'required',
                'order_items.*.service_type' => 'nullable|in:single,combo_5,combo_10',
                'order_items.*.quantity' => 'required|integer|min:1',
                'order_items.*.price' => 'required|numeric|min:0',
                'total_amount' => 'required|numeric|min:0',
                'discount_amount' => 'required|numeric|min:0',
                'note' => 'nullable|string'
            ]);

            DB::beginTransaction();

            // Determine initial status based on request type
            $initialStatus = $request->expectsJson() ? 'pending' : 'confirmed';

            // Create order
            $order = Order::create([
                'user_id' => $validatedData['user_id'],
                'total_amount' => $validatedData['total_amount'],
                'payment_method_id' => $validatedData['payment_method_id'],
                'voucher_id' => $validatedData['voucher_id'],
                'discount_amount' => $validatedData['discount_amount'],
                'note' => $validatedData['note'],
                'status' => $initialStatus
            ]);

            // Create order items
            foreach ($validatedData['order_items'] as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'item_type' => $item['item_type'],
                    'item_id' => $item['item_id'],
                    'service_type' => $item['service_type'] ?? null,
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                ]);

                // Reduce stock only for products
                if ($item['item_type'] === 'product') {
                    $product = Product::findOrFail($item['item_id']);
                    $this->productService->reduceStock(
                        $product,
                        $item['quantity'],
                        "Order #{$order->id}"
                    );
                }
            }

            DB::commit();
            return $this->respondWithJson($order->load('order_items'), 'Order created successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->respondWithError($e->getMessage());
        }
    }

    public function createInvoice(Order $order)
    {
        try {
            // Kiểm tra xem đơn hàng đã có hóa đơn chưa
            if ($order->invoice) {
                throw new \Exception('Đơn hàng này đã có hóa đơn');
            }

            // Tạo hóa đơn mới
            $invoice = Invoice::create([
                'user_id' => $order->user_id,
                'staff_user_id' => Auth::user()->id,
                'total_amount' => $order->total_amount - $order->discount_amount,
                'paid_amount' => 0, // remaining_amount sẽ tự động được tính
                'status' => Invoice::STATUS_PENDING,
                'order_id' => $order->id,
                'note' => request('note'),
                'created_by_user_id' => Auth::user()->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Hóa đơn đã được tạo thành công',
                'data' => $invoice->load(['user', 'staff', 'order'])
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function create()
    {
        return Inertia::render('Order/OrderCreate', [
            'paymentMethods' => PaymentMethod::all(),
            'vouchers' => Voucher::where('status', 'active')->get(),
        ]);
    }

    public function getMyOrders(Request $request)
    {
        try {
            $orders = Order::with(['order_items', 'invoice'])
                ->where('user_id', Auth::id())
                ->when($request->status, function ($query, $status) {
                    return $query->where('status', $status);
                })
                ->latest()
                ->paginate(10);

            return $this->respondWithJson($orders, 'Lấy danh sách đơn hàng thành công');
        } catch (\Exception $e) {
            return $this->respondWithJson(null, $e->getMessage(), 500);
        }
    }

    public function updateOrderStatus(Request $request, Order $order)
    {
        try {
            // Kiểm tra quyền
            if ($order->user_id !== Auth::id()) {
                return $this->respondWithJson(null, 'Bạn không có quyền cập nhật đơn hàng này', 403);
            }

            // Validate request
            $validated = $request->validate([
                'status' => 'required|in:cancelled',
                'cancel_reason' => 'nullable|string|max:500'
            ]);

            // Kiểm tra điều kiện hủy đơn
            if ($order->status !== 'pending') {
                return $this->respondWithJson(
                    null,
                    'Chỉ có thể hủy đơn hàng ở trạng thái chờ xử lý',
                    400
                );
            }

            // Cập nhật trạng thái
            $order->update([
                'status' => $validated['status'],
                'note' => $validated['cancel_reason'] ?? null
            ]);

            return $this->respondWithJson(
                $order->load(['order_items', 'invoice']),
                'Cập nhật trạng thái đơn hàng thành công'
            );
        } catch (\Exception $e) {
            return $this->respondWithJson(null, $e->getMessage(), 500);
        }
    }

    public function cancelOrder(Request $request, Order $order)
    {
        try {
            // Kiểm tra quyền
            if ($order->user_id !== Auth::id()) {
                return $this->respondWithJson(null, 'Bạn không có quyền hủy đơn hàng này', 403);
            }

            // Validate request
            $validated = $request->validate([
                'cancel_reason' => 'nullable|string|max:500'
            ]);

            // Kiểm tra điều kiện hủy đơn
            if ($order->status !== 'pending') {
                return $this->respondWithJson(
                    null,
                    'Chỉ có thể hủy đơn hàng ở trạng thái chờ xử lý',
                    400
                );
            }

            // Cập nhật trạng thái thành cancelled
            $order->update([
                'status' => 'cancelled',
                'note' => $validated['cancel_reason'] ?? null
            ]);

            return $this->respondWithJson(null, 'Hủy đơn hàng thành công');
        } catch (\Exception $e) {
            return $this->respondWithJson(null, $e->getMessage(), 500);
        }
    }
}
