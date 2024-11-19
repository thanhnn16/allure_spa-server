<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Service;
use App\Models\UserServicePackage;
use App\Models\UserVoucher;
use App\Models\Voucher;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\PaymentMethod;
use App\Models\Address;

class OrderService
{
    protected $firebaseService;
    protected $notificationService;

    public function __construct(
        FirebaseService $firebaseService,
        NotificationService $notificationService
    ) {
        $this->firebaseService = $firebaseService;
        $this->notificationService = $notificationService;
    }

    public function createOrder(array $data)
    {
        try {
            DB::beginTransaction();

            // Log request data để debug
            Log::info('Order creation data:', $data);

            // Validate order data first
            $this->validateOrderData($data);

            // Đảm bảo có user_id
            $userId = $data['user_id'] ?? Auth::id();

            // Tính toán với key order_items
            $calculatedTotals = $this->calculateOrderTotals($data['order_items']);

            // Validate and process voucher if provided
            $discountAmount = 0;
            if (isset($data['voucher_id'])) {
                $voucher = Voucher::findOrFail($data['voucher_id']);
                $this->validateVoucher($voucher, $calculatedTotals['subtotal'], Auth::id());
                $discountAmount = $this->calculateVoucherDiscount($voucher, $calculatedTotals['subtotal']);
                $this->updateVoucherUsage($voucher, Auth::id());
            }

            // Create order with calculated values
            $orderData = [
                'user_id' => $userId,
                'total_amount' => $calculatedTotals['subtotal'],
                'shipping_address_id' => $data['shipping_address_id'] ?? null,
                'payment_method_id' => $data['payment_method_id'],
                'status' => request()->is('api/*') ? Order::STATUS_PENDING : Order::STATUS_CONFIRMED,
                'discount_amount' => $discountAmount,
                'voucher_id' => $data['voucher_id'] ?? null,
                'note' => $data['note'] ?? null
            ];

            $order = Order::create($orderData);

            // Create order items with validated data
            foreach ($calculatedTotals['items'] as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'item_type' => $item['item_type'],
                    'item_id' => $item['item_id'],
                    'service_type' => $item['service_type'] ?? null,
                    'quantity' => $item['quantity'],
                    'price' => $item['unit_price'],
                ]);

                // Reserve stock for products
                if ($item['item_type'] === 'product') {
                    $product = Product::find($item['item_id']);
                    $product->decrement('quantity', $item['quantity']);
                }
            }

            // Verify final calculations
            $order->recalculateTotal();

            try {
                // Gửi thông báo cho admin
                $this->notificationService->notifyAdmins(
                    'Đơn hàng mới',
                    "Có đơn hàng mới từ khách hàng {$order->user->full_name}",
                    'new_order',
                    [
                        'type' => 'order',
                        'order_id' => $order->id,
                        'action' => 'created'
                    ]
                );

                // Gửi thông báo cho khách hàng
                $this->notificationService->createNotification([
                    'user_id' => $order->user_id,
                    'title' => 'Đặt hàng thành công',
                    'content' => "Đơn hàng #{$order->id} đã được tạo thành công",
                    'type' => 'order_created'
                ]);
            } catch (\Exception $e) {
                // Log lỗi nhưng không throw exception
                Log::error('Failed to send notifications:', [
                    'error' => $e->getMessage(),
                    'order_id' => $order->id
                ]);
            }

            DB::commit();
            return $order->load(['orderItems', 'user', 'shippingAddress', 'paymentMethod']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Order creation failed:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $data
            ]);
            throw $e;
        }
    }

    private function calculateOrderTotals(array $items)
    {
        $calculatedItems = [];
        $subtotal = 0;

        foreach ($items as $item) {
            // Get actual price based on item type
            if ($item['item_type'] === 'product') {
                $product = Product::findOrFail($item['item_id']);
                $unitPrice = $product->price;

                // Check stock
                if (!$product->hasStock($item['quantity'])) {
                    throw new \Exception("Sản phẩm {$product->name} không đủ số lượng trong kho");
                }
            } else {
                $service = Service::findOrFail($item['item_id']);
                $unitPrice = $this->getServicePrice($service, $item['service_type'] ?? 'single');
            }

            // Validate price matches
            if (abs($unitPrice - $item['price']) > 0.01) {
                throw new \Exception('Giá sản phẩm/dịch vụ không hợp lệ hoặc không đúng với giá hiện tại');
            }

            $itemTotal = $unitPrice * $item['quantity'];
            $subtotal += $itemTotal;

            $calculatedItems[] = array_merge($item, ['unit_price' => $unitPrice]);
        }

        return [
            'items' => $calculatedItems,
            'subtotal' => $subtotal
        ];
    }

    private function getServicePrice(Service $service, string $serviceType)
    {
        switch ($serviceType) {
            case 'single':
                return $service->single_price;
            case 'combo_5':
                return $service->combo_5_price;
            case 'combo_10':
                return $service->combo_10_price;
            default:
                throw new \Exception('Loại dịch vụ không hợp lệ');
        }
    }

    private function validateVoucher(Voucher $voucher, float $subtotal, string $userId)
    {
        // Check if voucher is active
        if (!$voucher->is_active) {
            throw new \Exception('Voucher không còn hiệu lực');
        }

        // Check minimum order value
        if ($subtotal < $voucher->min_order_value) {
            throw new \Exception('Giá trị đơn hàng chưa đạt giá trị tối thiểu để sử dụng voucher');
        }

        // Check user usage limit
        $userVoucher = UserVoucher::where('user_id', $userId)
            ->where('voucher_id', $voucher->id)
            ->first();

        if ($userVoucher && $userVoucher->remaining_uses <= 0) {
            throw new \Exception('Bạn đã hết lượt sử dụng voucher này');
        }
    }

    private function calculateVoucherDiscount(Voucher $voucher, float $subtotal)
    {
        $discountAmount = 0;

        if ($voucher->discount_type === 'percentage') {
            $discountAmount = $subtotal * ($voucher->discount_value / 100);
        } else {
            $discountAmount = $voucher->discount_value;
        }

        // Apply maximum discount limit if set
        if ($voucher->max_discount_amount > 0) {
            $discountAmount = min($discountAmount, $voucher->max_discount_amount);
        }

        return $discountAmount;
    }

    public function updateOrderStatus($order, $status, $note = null)
    {
        try {
            // Kiểm tra flow trạng thái hợp lệ
            if (!$this->isValidStatusTransition($order->status, $status)) {
                throw new \Exception('Không thể cập nhật sang trạng thái này');
            }

            // Kiểm tra ràng buộc với invoice
            if ($status === Order::STATUS_COMPLETED) {
                if (!$order->invoice) {
                    throw new \Exception('Không thể hoàn thành đơn hàng chưa có hóa đơn');
                }

                if ($order->invoice->status !== Invoice::STATUS_PAID) {
                    throw new \Exception('Không thể hoàn thành đơn hàng chưa thanh toán đủ');
                }
            }

            DB::beginTransaction();

            $updateData = [
                'status' => $status,
                'note' => $note
            ];

            // Thêm thông tin hủy đơn nếu status là cancelled
            if ($status === Order::STATUS_CANCELLED) {
                // Kiểm tra nếu đã thanh toán thì không được hủy
                if ($order->invoice && $order->invoice->status === Invoice::STATUS_PAID) {
                    throw new \Exception('Không thể hủy đơn hàng đã thanh toán');
                }

                $updateData = array_merge($updateData, [
                    'cancelled_by_user_id' => Auth::id(),
                    'cancelled_at' => now(),
                    'cancel_reason' => $note
                ]);
            }

            // Cập nhật đơn hàng
            $order->update($updateData);

            // Chuẩn bị thông báo dựa trên trạng thái
            $notificationData = $this->prepareStatusNotification($order, $status);

            try {
                // Gửi thông báo cho khách hàng
                if ($notificationData) {
                    $this->notificationService->createNotification([
                        'user_id' => $order->user_id,
                        'title' => $notificationData['title'],
                        'content' => $notificationData['content'],
                        'type' => 'order_status_changed'
                    ]);
                }

                // Gửi thông báo cho admin khi đơn hàng bị hủy
                if ($status === 'cancelled') {
                    $this->notificationService->notifyAdmins(
                        'Đơn hàng bị hủy',
                        "Đơn hàng #{$order->id} đã bị hủy bởi " .
                            (Auth::user()->role === 'admin' ? 'Admin' : 'khách hàng'),
                        'order_cancelled',
                        [
                            'type' => 'order',
                            'order_id' => $order->id,
                            'action' => 'cancelled'
                        ]
                    );
                }
            } catch (\Exception $e) {
                // Log lỗi nhưng không throw exception
                Log::error('Failed to send notifications:', [
                    'error' => $e->getMessage(),
                    'order_id' => $order->id
                ]);
            }

            DB::commit();
            return $order;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function prepareStatusNotification($order, $status)
    {
        $notifications = [
            'confirmed' => [
                'title' => 'Đơn hàng đã được xác nhận',
                'content' => "Đơn hàng #{$order->id} của bạn đã được xác nhận và đang được xử lý"
            ],
            'shipping' => [
                'title' => 'Đơn hàng đang được giao',
                'content' => "Đơn hàng #{$order->id} của bạn đang được giao đến bạn"
            ],
            'completed' => [
                'title' => 'Đơn hàng hoàn thành',
                'content' => "Đơn hàng #{$order->id} đã được giao thành công và hoàn thành. Cảm ơn bạn đã mua hàng!"
            ],
            'cancelled' => [
                'title' => 'Đơn hàng đã bị hủy',
                'content' => "Đơn hàng #{$order->id} đã bị hủy"
            ]
        ];

        return $notifications[$status] ?? null;
    }

    // Thêm method mới để kiểm tra flow trạng thái
    private function isValidStatusTransition($currentStatus, $newStatus)
    {
        // Định nghĩa flow cho phép
        $allowedTransitions = [
            Order::STATUS_PENDING => [Order::STATUS_CONFIRMED, Order::STATUS_CANCELLED],
            Order::STATUS_CONFIRMED => [Order::STATUS_SHIPPING, Order::STATUS_CANCELLED, Order::STATUS_COMPLETED],
            Order::STATUS_SHIPPING => [Order::STATUS_COMPLETED],
            Order::STATUS_COMPLETED => [], // Trạng thái cuối
            Order::STATUS_CANCELLED => [], // Trạng thái cuối
        ];

        return in_array($newStatus, $allowedTransitions[$currentStatus] ?? []);
    }

    public function completeOrder(Order $order)
    {
        DB::transaction(function () use ($order) {
            // Kiểm tra điều kiện để hoàn thành order
            if ($order->status === Order::STATUS_COMPLETED) {
                throw new \Exception('Đơn hàng đã được hoàn thành');
            }

            if ($order->status === Order::STATUS_CANCELLED) {
                throw new \Exception('Không thể hoàn thành đơn hàng đã hủy');
            }

            // Cập nhật trạng thái order
            $order->status = Order::STATUS_COMPLETED;
            $order->save();

            // Xử lý các order items và tạo service packages nếu chưa có
            foreach ($order->orderItems as $item) {
                if ($item->item_type === 'service' && $item->service_type) {
                    // Kiểm tra xem đã có service package chưa
                    $existingPackage = UserServicePackage::where([
                        'order_id' => $order->id,
                        'service_id' => $item->item_id,
                        'user_id' => $order->user_id
                    ])->first();

                    if (!$existingPackage) {
                        // Tạo gói dịch vụ cho khách hàng nếu chưa có
                        $this->createServicePackage($order, $item);
                    }
                }
            }

            try {
                // Gửi thông báo cho khách hàng
                $this->notificationService->createNotification([
                    'user_id' => $order->user_id,
                    'title' => 'Đơn hàng hoàn thành',
                    'content' => "Đơn hàng #{$order->id} của bạn đã hoàn thành",
                    'type' => 'order_completed'
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to send completion notification:', [
                    'error' => $e->getMessage(),
                    'order_id' => $order->id
                ]);
            }
        });

        return $order;
    }

    private function createServicePackage(Order $order, OrderItem $item)
    {
        $totalSessions = $this->getSessionsFromServiceType($item->service_type);
        $service = Service::find($item->item_id);

        UserServicePackage::create([
            'user_id' => $order->user_id,
            'service_id' => $item->item_id,
            'total_sessions' => $totalSessions,
            'used_sessions' => 0,
            'expiry_date' => now()->addDays($service->validity_period ?? 365),
            'is_combo' => true,
            'combo_type' => $this->mapServiceTypeToComboType($item->service_type),
            'order_id' => $order->id
        ]);
    }

    public function createInvoice(Order $order)
    {
        return Invoice::create([
            'order_id' => $order->id,
            'user_id' => $order->user_id,
            'total_amount' => $order->total_amount,
            'discount_amount' => $order->discount_amount,
            'final_amount' => $order->final_total,
            'payment_method_id' => $order->payment_method_id,
            'status' => Invoice::STATUS_PENDING,
            'created_by_user_id' => Auth::id()
        ]);
    }

    public function updateProductInventory(OrderItem $item)
    {
        $product = Product::find($item->item_id);
        if ($product) {
            if ($product->stock_quantity < $item->quantity) {
                throw new \Exception("Sản phẩm {$product->name} không đủ số lượng trong kho");
            }
            $product->decrement('stock_quantity', $item->quantity);
        }
    }

    public function updateLoyaltyPoints(Order $order)
    {
        $pointsEarned = floor($order->final_total / 1000); // 1 point per 1000
        $order->user->increment('loyalty_points', $pointsEarned);


        return $pointsEarned;
    }

    private function getSessionsFromServiceType(string $serviceType): int
    {
        return match ($serviceType) {
            'combo_5' => 5,
            'combo_10' => 10,
            default => 1
        };
    }

    private function mapServiceTypeToComboType(string $serviceType): ?string
    {
        return match ($serviceType) {
            'combo_5' => '5_times',
            'combo_10' => '10_times',
            default => null
        };
    }

    private function validateOrderData(array $data)
    {
        // Validate payment method
        $paymentMethod = PaymentMethod::find($data['payment_method_id']);
        if (!$paymentMethod) {
            throw new \Exception('Phương thức thanh toán không tồn tại');
        }

        // Validate user_id
        $userId = $data['user_id'] ?? Auth::id();
        if (!$userId) {
            throw new \Exception('Thiếu thông tin khách hàng');
        }

        // Kiểm tra user có tồn tại không
        $user = \App\Models\User::find($userId);
        if (!$user) {
            throw new \Exception('Không tìm thấy thông tin khách hàng');
        }

        // Validate order items
        if (!isset($data['order_items']) || empty($data['order_items'])) {
            throw new \Exception('Đơn hàng phải có ít nhất một sản phẩm hoặc dịch vụ');
        }

        // Validate từng item
        foreach ($data['order_items'] as $item) {
            if (!isset($item['item_type']) || !in_array($item['item_type'], ['product', 'service'])) {
                throw new \Exception('Loại item không hợp lệ');
            }

            if (!isset($item['item_id'])) {
                throw new \Exception('Thiếu ID sản phẩm/dịch vụ');
            }

            // Kiểm tra sự tồn tại của sản phẩm/dịch vụ
            if ($item['item_type'] === 'product') {
                $product = Product::find($item['item_id']);
                if (!$product) {
                    throw new \Exception("Sản phẩm với ID {$item['item_id']} không tồn tại");
                }
            } else {
                // Sửa phần này để kiểm tra dịch vụ
                $service = Service::where('id', $item['item_id'])->first();
                if (!$service) {
                    throw new \Exception("Dịch vụ với ID {$item['item_id']} không tồn tại");
                }

                // Validate giá dịch vụ
                $servicePrice = $this->getServicePrice($service, $item['service_type'] ?? 'single');
                if (abs($servicePrice - $item['price']) > 0.01) {
                    throw new \Exception("Giá dịch vụ không khớp với giá hiện tại");
                }
            }

            if (!isset($item['quantity']) || $item['quantity'] < 1) {
                throw new \Exception('Số lượng không hợp lệ');
            }

            // Validate service_type cho dịch vụ
            if ($item['item_type'] === 'service') {
                if (
                    !isset($item['service_type']) ||
                    !in_array($item['service_type'], ['single', 'combo_5', 'combo_10'])
                ) {
                    throw new \Exception('Loại dịch vụ không hợp lệ');
                }
            }
        }

        return true;
    }

    private function updateVoucherUsage(Voucher $voucher, string $userId)
    {
        DB::transaction(function () use ($voucher, $userId) {
            // Update voucher usage count
            $voucher->increment('used_times');

            // Update or create user voucher record
            $userVoucher = UserVoucher::firstOrNew([
                'user_id' => $userId,
                'voucher_id' => $voucher->id
            ]);

            if (!$userVoucher->exists) {
                $userVoucher->fill([
                    'remaining_uses' => $voucher->uses_per_user - 1,
                    'total_uses' => 1
                ]);
            } else {
                $userVoucher->increment('total_uses');
                $userVoucher->decrement('remaining_uses');
            }

            $userVoucher->save();
        });
    }

    // Thêm method mới để xử lý rollback stock khi cần
    private function rollbackProductStock(Order $order)
    {
        foreach ($order->orderItems as $item) {
            if ($item->item_type === 'product') {
                $product = Product::find($item->item_id);
                $product->increment('quantity', $item->quantity);
            }
        }
    }
}
