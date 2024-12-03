<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Invoice;
use App\Models\PaymentMethod;
use App\Models\Voucher;
use App\Models\Product;
use App\Services\ProductService;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

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
     *     required={"payment_method_id", "order_items", "total_amount"},
     *     @OA\Property(property="payment_method_id", type="integer"),
     *     @OA\Property(property="shipping_address_id", type="integer", nullable=true),
     *     @OA\Property(
     *         property="temporary_address",
     *         type="object",
     *         nullable=true,
     *         @OA\Property(property="province", type="string"),
     *         @OA\Property(property="district", type="string"), 
     *         @OA\Property(property="ward", type="string"),
     *         @OA\Property(property="address", type="string")
     *     ),
     *     @OA\Property(property="voucher_id", type="integer", nullable=true),
     *     @OA\Property(property="total_amount", type="number", format="float"),
     *     @OA\Property(property="discount_amount", type="number", format="float", nullable=true),
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


    protected $orderService;
    protected $productService;

    public function __construct(
        OrderService $orderService,
        ProductService $productService
    ) {
        $this->orderService = $orderService;
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
     *             enum={"pending", "confirmed", "shipping", "completed", "cancelled"}
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

    public function index(Request $request)
    {
        if (Auth::user()->role !== 'admin') {
            return $this->respondWithError('Bạn không có quyền truy cập', 403);
        }

        $orders = Order::with([
            'user',
            'invoice',
            'shippingAddress'
        ])
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

        if ($request->expectsJson()) {
            return $this->respondWithJson($orders, 'Lấy danh sách đơn hàng thành công');
        }

        return Inertia::render('Order/OrderView', [
            'orders' => $orders
        ]);
    }


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

    public function show(Request $request, Order $order)
    {
        $order->load([
            'user',
            'orderItems.product',
            'orderItems.service',
            'invoice',
            'shippingAddress',
            'paymentMethod',
            'voucher',
            'cancelledBy'
        ]);

        if ($request->expectsJson()) {
            if ($order->user_id !== Auth::user()->id) {
                return $this->respondWithError('Bạn không có quyền truy cập', 403);
            }
            return $this->respondWithJson($order, 'Lấy chi tiết đơn hàng thành công');
        }

        return Inertia::render('Order/OrderShow', [
            'order' => $order
        ]);
    }

    public function edit(Order $order)
    {
        $order->load(['user', 'orderItems']);
        return Inertia::render('Order/OrderEdit', [
            'order' => $order
        ]);
    }



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


    public function update(Request $request, Order $order)
    {
        try {
            $validated = $request->validate([
                'status' => 'required|in:pending,confirmed,shipping,completed,cancelled',
                'note' => 'nullable|string'
            ]);

            $updatedOrder = $this->orderService->updateOrderStatus(
                $order,
                $validated['status'],
                $validated['note']
            );

            return $this->respondWithJson($updatedOrder, 'Cập nhật trạng thái đơn hàng thành công');
        } catch (\Exception $e) {
            return $this->respondWithError($e->getMessage());
        }
    }

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

    public function destroy(Order $order)
    {
        $order->delete();
        return redirect()->route('orders.index')->with('success', 'Đơn hàng đã được xóa thành công.');
    }


    /**
     * @OA\Post(
     *     path="/api/orders",
     *     operationId="createOrder",
     *     summary="Tạo đơn hàng mới",
     *     description="Tạo một đơn hàng mới với các sản phẩm/dịch vụ",
     *     tags={"Orders"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Thông tin đơn hàng",
     *         @OA\JsonContent(
     *             required={"payment_method_id", "order_items"},
     *             @OA\Property(property="payment_method_id", type="integer", description="ID phương thức thanh toán"),
     *             @OA\Property(property="shipping_address_id", type="integer", nullable=true, description="ID địa chỉ giao hàng"),
     *             @OA\Property(property="voucher_id", type="integer", nullable=true, description="ID voucher áp dụng"),
     *             @OA\Property(property="note", type="string", nullable=true, description="Ghi chú đơn hàng"),
     *             @OA\Property(
     *                 property="order_items",
     *                 type="array",
     *                 description="Danh sách sản phẩm/dịch vụ trong đơn hàng",
     *                 @OA\Items(
     *                     @OA\Property(property="item_type", type="string", enum={"product", "service"}, description="Loại item"),
     *                     @OA\Property(property="item_id", type="integer", description="ID của sản phẩm/dịch vụ"),
     *                     @OA\Property(property="service_type", type="string", enum={"single", "combo_5", "combo_10"}, nullable=true, description="Loại dịch vụ (chỉ áp dụng cho dịch vụ)"),
     *                     @OA\Property(property="quantity", type="integer", minimum=1, description="Số lượng"),
     *                     @OA\Property(property="price", type="number", format="float", minimum=0, description="Đơn giá")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Tạo đơn hàng thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Đơn hàng đã được tạo thành công"),
     *             @OA\Property(
     *                 property="data",
     *                 ref="#/components/schemas/Order"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Dữ liệu không hợp lệ",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Dữ liệu không hợp lệ"),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 example={
     *                     "payment_method_id": {"Phương thức thanh toán không được để trống"},
     *                     "order_items": {"Đơn hàng phải có ít nhất một sản phẩm/dịch vụ"}
     *                 }
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Lỗi nghiệp vụ",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Sản phẩm không đủ số lượng trong kho")
     *         )
     *     )
     * )
     */

    /**
     * @OA\Schema(
     *     schema="OrderResponse",
     *     title="Order Response",
     *     description="Chi tiết đơn hàng trong response",
     *     @OA\Property(property="id", type="integer", example=789),
     *     @OA\Property(property="user_id", type="string", format="uuid"),
     *     @OA\Property(property="total_amount", type="number", format="float"),
     *     @OA\Property(property="discount_amount", type="number", format="float"),
     *     @OA\Property(
     *         property="status",
     *         type="string",
     *         enum={"pending", "confirmed", "shipping", "completed", "cancelled"}
     *     ),
     *     @OA\Property(property="shipping_address_id", type="integer", nullable=true),
     *     @OA\Property(property="payment_method_id", type="integer"),
     *     @OA\Property(property="voucher_id", type="integer", nullable=true),
     *     @OA\Property(property="note", type="string", nullable=true),
     *     @OA\Property(
     *         property="order_items",
     *         type="array",
     *         @OA\Items(ref="#/components/schemas/OrderItem")
     *     ),
     *     @OA\Property(property="user", ref="#/components/schemas/User"),
     *     @OA\Property(property="shipping_address", ref="#/components/schemas/Address"),
     *     @OA\Property(property="payment_method", ref="#/components/schemas/PaymentMethod"),
     *     @OA\Property(property="created_at", type="string", format="date-time"),
     *     @OA\Property(property="updated_at", type="string", format="date-time")
     * )
     */

    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'user_id' => 'nullable|exists:users,id',
                'payment_method_id' => 'required|exists:payment_methods,id',
                'shipping_address_id' => 'nullable|exists:addresses,id',
                'temporary_address' => 'nullable|array',
                'temporary_address.province' => 'required_with:temporary_address',
                'temporary_address.district' => 'required_with:temporary_address',
                'temporary_address.ward' => 'required_with:temporary_address',
                'temporary_address.address' => 'required_with:temporary_address',
                'voucher_id' => 'nullable|exists:vouchers,id',
                'note' => 'nullable|string|max:500',
                'order_items' => 'required|array|min:1',
                'order_items.*.item_type' => 'required|in:product,service',
                'order_items.*.item_id' => 'required|integer',
                'order_items.*.service_type' => 'nullable|required_if:order_items.*.item_type,service|in:single,combo_5,combo_10',
                'order_items.*.quantity' => 'required|integer|min:1',
                'order_items.*.price' => 'required|numeric|min:0',
            ]);

            $order = $this->orderService->createOrder($validatedData);

            return $this->respondWithJson(
                $order,
                'Đơn hàng đã được tạo thành công',
                201
            );
        } catch (\Exception $e) {
            Log::error('Order creation failed:', [
                'request' => $request->all(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return $this->respondWithError(
                $e->getMessage(),
                $e instanceof ValidationException ? 422 : 400
            );
        }
    }


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


    public function createInvoice(Order $order)
    {
        try {
            if ($order->invoice) {
                throw new \Exception('Đơn hàng này đã có hóa đơn');
            }

            $invoice = $this->orderService->createInvoice($order);

            // Cập nhật điểm thưởng cho khách hàng
            $this->orderService->updateLoyaltyPoints($order);

            return $this->respondWithJson(
                $invoice->load(['user', 'staff', 'order']),
                'Hóa đơn đã được tạo thành công'
            );
        } catch (\Exception $e) {
            return $this->respondWithError($e->getMessage());
        }
    }

    public function create()
    {
        return Inertia::render('Order/OrderCreate', [
            'paymentMethods' => PaymentMethod::all(),
            'vouchers' => Voucher::where('status', 'active')->get(),
        ]);
    }

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
     *         @OA\Schema(type="string", enum={"pending", "confirmed", "shipping", "completed", "cancelled"})
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


    public function getMyOrders(Request $request)
    {
        try {
            $orders = Order::with([
                'orderItems',
                'invoice',
                'shippingAddress' // Thêm relationship này
            ])
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
                $order->load(['orderItems', 'invoice']),
                'Cập nhật trạng thái đơn hàng thành công'
            );
        } catch (\Exception $e) {
            return $this->respondWithJson(null, $e->getMessage(), 500);
        }
    }

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

    public function cancelOrder(Request $request, Order $order)
    {
        try {
            // Kiểm tra quyền
            if ($order->user_id !== Auth::id() && Auth::user()->role !== 'admin') {
                return $this->respondWithJson(null, 'Bạn không có quyền hủy đơn hàng này', 403);
            }

            // Validate request
            $validated = $request->validate([
                'cancel_reason' => 'nullable|string|max:500'
            ]);

            // Kiểm tra điều kiện hủy đơn
            if (!in_array($order->status, ['pending', 'confirmed'])) {
                return $this->respondWithJson(
                    null,
                    'Chỉ có thể hủy đơn hàng ở trạng thái chờ xử lý hoặc đã xác nhận',
                    400
                );
            }

            // Cập nhật trạng thái thành cancelled
            $order->update([
                'status' => 'cancelled',
                'cancel_reason' => $validated['cancel_reason'] ?? null,
                'cancelled_by_user_id' => Auth::id(),
                'cancelled_at' => now()
            ]);

            return $this->respondWithJson(
                $order->load(['cancelledBy']),
                'Hủy đơn hàng thành công'
            );
        } catch (\Exception $e) {
            return $this->respondWithJson(null, $e->getMessage(), 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/orders/{order}/complete",
     *     summary="Complete an order (Admin only)",
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
     *         description="Order completed successfully"
     *     )
     * )
     */
    public function complete(Request $request, Order $order)
    {
        try {
            if (Auth::user()->role !== 'admin') {
                return $this->respondWithError('Bạn không có quyền thực hiện hành động này', 403);
            }

            // Add validation check
            if (!$order->canBeCompleted()) {
                return $this->respondWithError('Không thể hoàn thành đơn hàng này. Vui lòng kiểm tra lại điều kiện.', 400);
            }

            $completedOrder = $this->orderService->completeOrder($order);

            // Update inventory for products
            foreach ($order->orderItems as $item) {
                if ($item->item_type === 'product') {
                    $this->orderService->updateProductInventory($item);
                }
            }

            return $this->respondWithJson(
                $completedOrder->fresh(),
                'Đơn hàng đã hoàn thành thành công'
            );
        } catch (\Exception $e) {
            return $this->respondWithError($e->getMessage(), 400);
        }
    }
}
