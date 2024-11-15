<?php

use App\Http\Controllers\AddressController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\RatingController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PayOSController;
use App\Http\Controllers\ZaloAuthController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\TimeSlotController;
use App\Http\Controllers\FirebaseWebhookController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\AiConfigController;
use App\Http\Controllers\AiFunctionController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\VoucherController;


Route::middleware('throttle:api')->group(function () {
    // Test route '/' return 'Hello World'
    Route::get('/hello-world', function () {
        return 'Hello World';
    });

    // Auth routes
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::post('/auth/change-password', [AuthController::class, 'changePassword']);

    // Search routes
    Route::get('/products/search', [ProductController::class, 'searchProducts']);
    Route::get('/services/search', [ServiceController::class, 'searchServices']);

    Route::get('/search', [SearchController::class, 'search']);


    // Service routes
    Route::get('/services', [ServiceController::class, 'index']);
    Route::get('/services/appointment', [ServiceController::class, 'getServicesForAppointment']);
    Route::get('/services/{service}', [ServiceController::class, 'show']);
    Route::get('/service-categories', [ServiceController::class, 'categories']);

    // Product routes
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{product}', [ProductController::class, 'show']);
    Route::get('/product-categories', [ProductController::class, 'categories']);

    Route::post('/payos/test', [PayOSController::class, 'testPayment']);
    Route::post('/payos/verify', [PayOSController::class, 'verifyPayment']);

    Route::post('/zalo/generate-code-verifier', [ZaloAuthController::class, 'generateCodeVerifier']);
    Route::post('/zalo/callback', [ZaloAuthController::class, 'callback']);


    Route::middleware(['auth:sanctum'])->group(function () {
        Route::post('/fcm/token', [AuthController::class, 'storeFcmToken']);

        // Appointment routes
        Route::get('/appointments', [AppointmentController::class, 'index']);
        Route::get('/appointments/my-appointments', [AppointmentController::class, 'getMyAppointments']);
        Route::post('/appointments', [AppointmentController::class, 'store']);
        Route::get('/appointments/{appointment}', [AppointmentController::class, 'show']);
        Route::put('/appointments/{appointment}', [AppointmentController::class, 'update']);
        Route::delete('/appointments/{appointment}', [AppointmentController::class, 'destroy']);
        Route::get('/my-appointments', [AppointmentController::class, 'getMyAppointments']);

        // Thêm các route mới cho cập nhật và hủy cuộc hẹn
        Route::put('/appointments/{appointment}/update', [AppointmentController::class, 'update']);
        Route::put('/appointments/{appointment}/cancel', [AppointmentController::class, 'cancel']);

        // User routes
        Route::get('/users/search', [UserController::class, 'searchUsers']);
        Route::get('/users/get-staff-list', [UserController::class, 'getStaffList']);
        Route::get('/users/{userId}/service-packages', [UserController::class, 'getUserServicePackages']);

        Route::get('/user-service-packages/{user}', [UserController::class, 'getUserServicePackages']);

        Route::post('/import', [ImportController::class, 'importAll']);

        Route::get('/dashboard', [DashboardController::class, 'index']);

        Route::get('/user/info', [UserController::class, 'getUserInfo']);

        // User address routes
        Route::get('/user/addresses', [AddressController::class, 'index']);
        Route::post('/user/addresses', [AddressController::class, 'store']);
        Route::put('/user/addresses/{address}', [AddressController::class, 'update']);
        Route::delete('/user/addresses/{address}', [AddressController::class, 'destroy']);
        Route::get('/user/my-addresses', [AddressController::class, 'getAddressByUser']);

        // Rating routes
        Route::get('/ratings', [RatingController::class, 'index']);
        Route::get('/products/{productId}/ratings', [RatingController::class, 'getProductRatings']);
        Route::get('/services/{serviceId}/ratings', [RatingController::class, 'getServiceRatings']);
        Route::post('/ratings', [RatingController::class, 'store']);

        Route::get('/products/{productId}/approved-ratings', [RatingController::class, 'getApprovedProductRatings']);
        Route::get('/services/{serviceId}/approved-ratings', [RatingController::class, 'getApprovedServiceRatings']);
        Route::post('/ratings/from-order', [RatingController::class, 'storeFromOrder']);
        Route::put('/ratings/{id}', [RatingController::class, 'update']);

        // Invoice routes
        Route::get('/invoices', [InvoiceController::class, 'index']);
        Route::post('/invoices', [InvoiceController::class, 'store']);
        Route::get('/invoices/{invoice}', [InvoiceController::class, 'show']);
        Route::put('/invoices/{invoice}', [InvoiceController::class, 'update']);
        Route::delete('/invoices/{invoice}', [InvoiceController::class, 'destroy']);


        // Chat routes
        Route::get('/chats', [ChatController::class, 'index']);
        Route::post('/chats', [ChatController::class, 'store']);
        Route::get('/chats/{chat}/messages', [ChatController::class, 'getMessages']);
        Route::post('/messages', [ChatController::class, 'sendMessage']);
        Route::post('/auth/fcm-token', [AuthController::class, 'storeFcmToken']);
        Route::post('/chats/{chat}/mark-as-read', [ChatController::class, 'markAsRead']);

        // Add logout route
        Route::post('/auth/logout', [AuthController::class, 'logout']);

        // Add time slot routes
        Route::get('/time-slots', [TimeSlotController::class, 'index']);
        Route::get('/time-slots/available', [TimeSlotController::class, 'getAvailableSlots']);

        // Product routes
        Route::put('/products/{product}', [ProductController::class, 'update'])->name('api.products.update');

        // Profile route
        Route::get('/profile', [UserController::class, 'profile']);

        // Sửa route upload avatar từ /users/upload-avatar thành /user/avatar
        Route::post('/user/avatar', [UserController::class, 'uploadAvatar']);

        // PayOS routes
        Route::prefix('payos')->group(function () {
            Route::post('/process', [PayOSController::class, 'processPayment']);
        });

        // Invoice routes
        Route::prefix('invoices')->group(function () {
            Route::get('/{invoice}/payment', [InvoiceController::class, 'getPaymentDetails']);
            Route::post('/{invoice}/pay', [PayOSController::class, 'processPayment']);
            Route::post('/{invoice}/payos', [PayOSController::class, 'createPaymentLinkForInvoice']);
            Route::post('/{invoiceId}/payos', [PayOSController::class, 'createPaymentLinkForInvoice']);
        });

        // Favorite routes
        Route::post('/favorites/toggle', [FavoriteController::class, 'toggle']);
        Route::get('/favorites', [FavoriteController::class, 'index']);
        Route::get('/favorites/{type}', [FavoriteController::class, 'getByType']);

        // Sửa lại routes cho AI Config
        Route::prefix('ai-configs')->group(function () {
            Route::get('/', [AiConfigController::class, 'index']);
            Route::post('/', [AiConfigController::class, 'store']);
            Route::put('/{id}', [AiConfigController::class, 'update']);
            Route::delete('/{id}', [AiConfigController::class, 'destroy']);
            Route::post('/upload', [AiConfigController::class, 'upload']);
            Route::post('/global-api-key', [AiConfigController::class, 'updateGlobalApiKey']);
            Route::get('/global-api-key', [AiConfigController::class, 'getGlobalApiKey']);
        });

        // AI Function routes
        Route::prefix('ai')->group(function () {
            Route::post('/function-call', [AiFunctionController::class, 'handleFunctionCall']);
            Route::get('/available-functions', [AiFunctionController::class, 'getAvailableFunctions']);
        });

        // Order routes
        Route::prefix('orders')->group(function () {
            // Basic CRUD operations
            Route::get('/', [OrderController::class, 'index']);
            Route::post('/', [OrderController::class, 'store']);
            Route::get('/my-orders', [OrderController::class, 'getMyOrders']);
            Route::get('/{order}', [OrderController::class, 'show']);
            Route::put('/{order}', [OrderController::class, 'update']);
            Route::delete('/{order}', [OrderController::class, 'destroy']);

            // Order status management
            Route::put('/{order}/update-status', [OrderController::class, 'updateOrderStatus']);
            Route::delete('/{order}/cancel', [OrderController::class, 'cancelOrder']);

            // Payment related
            Route::post('/{order}/payment', [PayOSController::class, 'createPaymentLink']);
            Route::post('/{order}/verify-payment', [PayOSController::class, 'verifyPayment']);

            // User specific
            Route::get('/my-orders', [OrderController::class, 'getMyOrders']);

            // Thêm route tạo hóa đơn từ đơn hàng
            Route::post('/{order}/create-invoice', [OrderController::class, 'createInvoice']);

            // Thay vì gọi trực tiếp createPaymentLink
            Route::post('/orders/{order}/payment-link', [PayOSController::class, 'processPayment'])
                ->middleware('auth:sanctum');
        });

        // Notification routes
        Route::prefix('notifications')->group(function () {
            Route::get('/', [NotificationController::class, 'index']);
            Route::post('/{id}/mark-as-read', [NotificationController::class, 'markAsRead']);
            Route::post('/mark-all-as-read', [NotificationController::class, 'markAllAsRead']);
            Route::get('/unread-count', [NotificationController::class, 'getUnreadCount']);
        });

        // Add new route for stats
        Route::get('/dashboard/stats', [DashboardController::class, 'stats']);

        // Voucher routes
        Route::prefix('vouchers')->group(function () {
            Route::get('/', [VoucherController::class, 'index']);
            Route::get('/my-vouchers', [VoucherController::class, 'getMyVouchers']);
            Route::get('/user/{userId}', [VoucherController::class, 'getUserVouchers']);
            Route::post('/{id}/toggle-status', [VoucherController::class, 'toggleStatus']);
        });

        // Sửa lại route cập nhật user profile
        Route::put('/user/profile', [UserController::class, 'updateProfile']);
    });

    Route::post('firebase/webhook', [FirebaseWebhookController::class, 'handleMessage']);

    // Add new route for checking available slots
    Route::get('/time-slots/available', [TimeSlotController::class, 'getAvailableSlots']);
});

Route::prefix('payos')->group(function () {
    Route::post('/test', [PayOSController::class, 'testPayment']);
    Route::post('/verify', [PayOSController::class, 'verifyPayment']);
});
