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

            // Xử lý địa chỉ giao hàng
            $shippingAddressId = $this->handleShippingAddress($data, $userId);

            // Tính toán với key order_items
            $calculatedTotals = $this->calculateOrderTotals($data['order_items']);
            $subtotal = $calculatedTotals['subtotal'];

            // Xử lý voucher và tính discount amount
            $discountAmount = 0;
            if (!empty($data['voucher_id'])) {
                $voucher = Voucher::findOrFail($data['voucher_id']);

                // Validate voucher
                $this->validateVoucher($voucher, $subtotal, $userId);

                // Tính discount amount
                $discountAmount = $this->calculateVoucherDiscount($voucher, $subtotal);

                // Cập nhật sử dụng voucher
                $this->updateVoucherUsage($voucher, $userId);
            }

            // Tạo order data với discount amount đã tính
            $orderData = [
                'user_id' => $userId,
                'shipping_address_id' => $shippingAddressId,
                'payment_method_id' => $data['payment_method_id'],
                'status' => request()->is('api/*') ? Order::STATUS_PENDING : Order::STATUS_CONFIRMED,
                'total_amount' => $subtotal,
                'discount_amount' => $discountAmount,
                'final_amount' => $subtotal - $discountAmount,
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
                // Gửi thông báo cho khách hàng
                $this->notificationService->createNotification([
                    'user_id' => $order->user_id,
                    'type' => 'order_new',
                    'data' => [
                        'id' => $order->id
                    ],
                    'send_fcm' => true
                ]);

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
            } catch (\Exception $e) {
                // Log lỗi nhưng không throw exception
                Log::error('Failed to send notifications:', [
                    'error' => $e->getMessage(),
                    'order_id' => $order->id
                ]);
            }

            DB::commit();

            // Tạo hóa đơn sau khi tạo đơn hàng thành công
            $this->createInvoice($order);

            return $order->load(['orderItems', 'user', 'shippingAddress', 'paymentMethod', 'voucher']);
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
        // Kiểm tra voucher có active không
        if (!$voucher->is_active) {
            throw new \Exception('Voucher không còn hiệu lực');
        }

        // Kiểm tra giá trị đơn hàng tối thiểu
        if ($subtotal < $voucher->min_order_value) {
            throw new \Exception("Giá trị đơn hàng tối thiểu phải từ {$voucher->min_order_value_formatted} để sử dụng voucher này");
        }

        // Kiểm tra số lần sử dụng của user
        $userVoucher = UserVoucher::where('user_id', $userId)
            ->where('voucher_id', $voucher->id)
            ->first();

        if ($userVoucher && $userVoucher->remaining_uses <= 0) {
            throw new \Exception('Bạn đã hết lượt sử dụng voucher này');
        }

        // Kiểm tra số lượng voucher còn lại
        if (!$voucher->is_unlimited && $voucher->remaining_uses <= 0) {
            throw new \Exception('Voucher đã hết lượt sử dụng');
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
            DB::beginTransaction();

            // Kiểm tra điều kiện hủy đơn
            if ($status === Order::STATUS_CANCELLED) {
                if (!in_array($order->status, ['pending', 'confirmed'])) {
                    throw new \Exception('Chỉ có thể hủy đơn hàng ở trạng thái chờ xử lý hoặc đã xác nhận');
                }

                // Cập nhật thông tin hủy đơn
                $updateData = [
                    'status' => $status,
                    'cancel_reason' => $note,
                    'cancelled_by_user_id' => Auth::id(),
                    'cancelled_at' => now()
                ];

                // Nếu có hóa đơn, hủy hóa đơn
                if ($order->invoice) {
                    $order->invoice->update([
                        'status' => Invoice::STATUS_CANCELLED,
                        'cancelled_at' => now(),
                        'cancelled_by_user_id' => Auth::id()
                    ]);

                    // Tạo payment history cho hóa đơn
                    $order->invoice->paymentHistories()->create([
                        'old_payment_status' => $order->invoice->status,
                        'new_payment_status' => Invoice::STATUS_CANCELLED,
                        'payment_amount' => 0,
                        'payment_method' => 'system',
                        'created_by_user_id' => Auth::id(),
                        'note' => 'Hóa đơn đã bị hủy do đơn hàng bị hủy'
                    ]);
                }

                // Hoàn lại số lượng sản phẩm nếu có
                $this->rollbackProductStock($order);
            } else {
                $updateData = [
                    'status' => $status,
                    'note' => $note
                ];
            }

            // Cập nhật đơn hàng
            $order->update($updateData);

            // Gửi thông báo
            try {
                $this->notificationService->createNotification([
                    'user_id' => $order->user_id,
                    'type' => 'order_status_changed',
                    'data' => [
                        'order_id' => $order->id,
                        'old_status' => $order->getOriginal('status'),
                        'new_status' => $status
                    ],
                    'send_fcm' => true
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to send notification:', [
                    'error' => $e->getMessage(),
                    'order_id' => $order->id
                ]);
            }

            DB::commit();
            return $order->fresh(['invoice', 'cancelledBy']);
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
        try {
            DB::beginTransaction();

            if ($order->status === Order::STATUS_CANCELLED) {
                throw new \Exception('Không thể hoàn thành đơn hàng đã hủy');
            }

            // Kiểm tra invoice và trạng thái thanh toán
            if (!$order->invoice || $order->invoice->status !== Invoice::STATUS_PAID) {
                throw new \Exception('Không thể hoàn thành đơn hàng chưa thanh toán đủ');
            }

            // Kiểm tra và tạo service packages cho các service combo
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

            // Cập nhật trạng thái order
            $order->status = Order::STATUS_COMPLETED;
            $order->save();

            try {
                // Gửi thông báo cho khách hàng
                $this->notificationService->createNotification([
                    'user_id' => $order->user_id,
                    'type' => 'order_completed',
                    'data' => [
                        'id' => $order->id
                    ],
                    'send_fcm' => true
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to send completion notification:', [
                    'error' => $e->getMessage(),
                    'order_id' => $order->id
                ]);
            }

            DB::commit();
            return $order->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function createServicePackage(Order $order, OrderItem $item)
    {
        // Tính tổng số buổi dựa trên loại combo và số lượng đặt
        $sessionsPerCombo = $this->getSessionsFromServiceType($item->service_type);
        $totalSessions = $sessionsPerCombo * $item->quantity;

        $service = Service::find($item->item_id);

        UserServicePackage::create([
            'user_id' => $order->user_id,
            'service_id' => $item->item_id,
            'total_sessions' => $totalSessions, // Số buổi đã được nhân với quantity
            'used_sessions' => 0,
            'expiry_date' => now()->addDays($service->validity_period ?? 365),
            'is_combo' => true,
            'combo_type' => $this->mapServiceTypeToComboType($item->service_type),
            'order_id' => $order->id
        ]);
    }

    public function createInvoice(Order $order)
    {
        // Thêm logging để debug
        Log::info('Creating invoice with data:', [
            'order_id' => $order->id,
            'user_id' => $order->user_id,
            'total_amount' => $order->final_amount,
            'subtotal' => $order->total_amount,
            'discount' => $order->discount_amount,
            'payment_method_id' => $order->payment_method_id
        ]);

        // Tính toán final_amount một cách rõ ràng
        $finalAmount = $order->total_amount - ($order->discount_amount ?? 0);

        return Invoice::create([
            'order_id' => $order->id,
            'user_id' => $order->user_id,
            'total_amount' => $finalAmount, // Sử dụng giá trị đã tính toán
            'paid_amount' => 0,
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

    // Thêm method mới để lấy translation cho order status
    private function getOrderStatusTranslation($status)
    {
        $translations = [
            'confirmed' => [
                'en' => 'confirmed',
                'vi' => 'đã xác nhận',
                'ja' => '確認済み'
            ],
            'shipping' => [
                'en' => 'being shipped',
                'vi' => 'đang giao hàng',
                'ja' => '配送中'
            ],
            'completed' => [
                'en' => 'completed',
                'vi' => 'đã hoàn thành',
                'ja' => '完了'
            ],
            'cancelled' => [
                'en' => 'cancelled',
                'vi' => 'đã hủy',
                'ja' => 'キャンセル'
            ]
        ];

        return $translations[$status] ?? $status;
    }

    private function handleShippingAddress(array $data, string $userId)
    {
        // Nếu có temporary_address
        if (isset($data['temporary_address'])) {
            // Tạo địa chỉ tạm thời
            $address = Address::create([
                'user_id' => $userId,
                'province' => $data['temporary_address']['province'],
                'district' => $data['temporary_address']['district'],
                'ward' => $data['temporary_address']['ward'],
                'address' => $data['temporary_address']['address'],
                'address_type' => 'shipping',
                'is_temporary' => true,
                'is_default' => false
            ]);

            return $address->id;
        }

        // Nếu có shipping_address_id
        if (isset($data['shipping_address_id'])) {
            // Validate địa chỉ có tồn tại và thuộc về user
            $address = Address::where('id', $data['shipping_address_id'])
                ->where('user_id', $userId)
                ->first();

            if (!$address) {
                throw new \Exception('Địa chỉ giao hàng không hợp lệ');
            }

            return $address->id;
        }

        // Lấy địa chỉ mặc định của người dùng nếu không có địa chỉ nào được cung cấp
        $defaultAddress = Address::where('user_id', $userId)
            ->where('is_default', true)
            ->first();

        return $defaultAddress ? $defaultAddress->id : null;
    }
}
