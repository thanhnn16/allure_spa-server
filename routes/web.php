<?php

use App\Http\Controllers\AddressController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProductController;
use App\Models\ProductCategory;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\VoucherController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\RatingController;
use App\Http\Controllers\StockMovementController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ZaloAuthController;
use App\Http\Controllers\PayOSController;
use Illuminate\Support\Facades\Broadcast;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\AiConfigController;
use App\Http\Controllers\BannerController;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;

Route::get('/', function () {
    if (Auth::check()) {
        return redirect('/dashboard');
    }
    return redirect()->route('login');
})->name('index');

Route::get('/dashboard', function () {
    return Inertia::render('HomeView', [
        'canLogin' => Route::has('login'),
        'user' => Auth::user(),
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/zalo-login-progress', [ZaloAuthController::class, 'index'])->name('zalo.login.progress');

Route::middleware('auth')->group(function () {
    // User routes
    Route::resource('users', UserController::class);
    Route::post('/users/import', [UserController::class, 'import'])->name('users.import');

    // Profile routes
    Route::get('/profile/', [UserController::class, 'profile'])->name('users.profile');
    Route::patch('/profile/{profile}', [UserController::class, 'updateProfile'])->name('users.updateProfile');

    // Product routes
    Route::resource('products', ProductController::class);
    Route::get('products/faith', [ProductController::class, 'faith'])->name('products.faith');

    // Category routes
    Route::resource('categories', ProductCategory::class);

    // Invoice routes
    Route::resource('invoices', InvoiceController::class);
    Route::post('/invoices/{invoice}/cancel', [InvoiceController::class, 'cancel'])
        ->name('invoices.cancel');

    // Order routes
    Route::resource('orders', OrderController::class);
    Route::post('/orders/{order}/create-invoice', [OrderController::class, 'createInvoice'])
        ->name('orders.create-invoice');

    // Voucher routes
    Route::resource('vouchers', VoucherController::class);
    Route::prefix('vouchers')->group(function () {
        Route::post('/assign-to-user', [VoucherController::class, 'assignToUser']);
        Route::get('/user/{userId}/vouchers', [VoucherController::class, 'getUserVouchers']);
    });

    // Service routes
    Route::resource('services', ServiceController::class);
    Route::get('/api/services/appointment', [ServiceController::class, 'getServicesForAppointment']);

    // Service Category routes

    // Appointment routes
    Route::resource('appointments', AppointmentController::class);

    // Favorite routes

    // Cart routes
    Route::resource('carts', CartController::class);

    // Rating routes
    Route::resource('ratings', RatingController::class);


    // Stock Movement routes
    Route::resource('stock-movements', StockMovementController::class);

    // Notification routes
    Route::resource('notifications', NotificationController::class);
    Route::post('/notifications/{id}/mark-as-read', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/mark-all-as-read', [NotificationController::class, 'markAllAsRead']);

    // Mobile App routes - Web Interface
    Route::get('banners', [BannerController::class, 'index'])->name('banners.web');
    // Không cần các route khác vì sẽ xử lý qua API

    // Report routes
    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('reports/revenue', [ReportController::class, 'revenue'])->name('reports.revenue');
    Route::get('reports/profit', [ReportController::class, 'profit'])->name('reports.profit');
    Route::get('reports/customers', [ReportController::class, 'customers'])->name('reports.customers');
    Route::get('reports/staff', [ReportController::class, 'staff'])->name('reports.staff');
    Route::get('reports/stock', [ReportController::class, 'stock'])->name('reports.stock');
    Route::get('reports/appointments', [ReportController::class, 'appointments'])->name('reports.appointments');
    Route::get('reports/invoices', [ReportController::class, 'invoices'])->name('reports.invoices');
    Route::get('reports/ai', [ReportController::class, 'ai'])->name('reports.ai');

    // Invoice payment routes
    Route::post('/invoices/{invoice}/process-payment', [InvoiceController::class, 'processPayment'])
        ->name('invoices.process-payment');
    Route::post('/invoices/{invoice}/pay-with-payos', [InvoiceController::class, 'payWithPayOS'])
        ->name('invoices.pay-with-payos');

    // PayOS routes
    Route::get('/payment/test', [PayOSController::class, 'showPaymentStatus'])
        ->name('payment.test');
    Route::post('/api/payos/verify', [PayOSController::class, 'verifyPayment'])
        ->name('payos.verify');

    Route::get('/success', function () {
        return Inertia::render('Payment/PaymentTest', [
            'status' => 'success',
            'transactionId' => request('orderCode')
        ]);
    })->name('payment.success');

    Route::get('/cancel', function () {
        return Inertia::render('Payment/PaymentTest', [
            'status' => 'cancel'
        ]);
    })->name('payment.cancel');


    // Chat routes
    Route::get('/chats', [ChatController::class, 'index'])->name('chats.index');
    Route::post('/chats', [ChatController::class, 'store'])->name('chats.store');
    Route::get('/chats/{chat}/messages', [ChatController::class, 'getMessages'])->name('chats.messages');
    Route::post('/chats/send', [ChatController::class, 'sendMessage'])->name('chats.send');
    Route::post('/chats/{chat}/mark-as-read', [ChatController::class, 'markAsRead'])->name('chats.mark-read');

    // Add broadcasting auth route
    Broadcast::routes();

    // Add new route for user treatment packages
    Route::get('/api/user-treatment-packages/{userId}', [UserController::class, 'getUserTreatmentPackages'])
        ->name('api.user.treatment-packages');

    // Product routes
    Route::post('/products/{product}/upload-images', [ProductController::class, 'uploadImages'])
        ->name('products.upload-images');

    // Media routes
    Route::put('/media/reorder', [MediaController::class, 'reorder'])->name('media.reorder');
    Route::delete('/media/{media}', [MediaController::class, 'destroy'])->name('media.destroy');

    // Address routes
    Route::resource('addresses', AddressController::class);

    // AiConfig routes
    Route::prefix('ai-config')->group(function () {
        Route::get('/', [AiConfigController::class, 'index'])->name('ai-config.index');
        Route::post('/', [AiConfigController::class, 'store'])->name('ai-config.store');
        Route::put('/{id}', [AiConfigController::class, 'update'])->name('ai-config.update');
        Route::delete('/{id}', [AiConfigController::class, 'destroy'])->name('ai-config.destroy');
        Route::post('/upload', [AiConfigController::class, 'upload'])->name('ai-config.upload');
    });
});

Route::get('/firebase-messaging-sw.js', function () {
    $config = Config::get('firebase');

    // Convert config to JavaScript object
    $firebaseConfig = json_encode([
        'apiKey' => $config['api_key'],
        'authDomain' => $config['auth_domain'],
        'projectId' => $config['project_id'],
        'storageBucket' => $config['storage_bucket'],
        'messagingSenderId' => $config['messaging_sender_id'],
        'appId' => $config['app_id'],
    ]);

    $content = File::get(public_path('firebase-messaging-sw.js'));
    $content = str_replace('FIREBASE_CONFIG', $firebaseConfig, $content);

    return response($content)
        ->header('Content-Type', 'application/javascript');
});

require __DIR__ . '/auth.php';
