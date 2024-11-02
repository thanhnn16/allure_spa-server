<?php

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
use App\Http\Controllers\MobileAppController;
use App\Http\Controllers\RatingController;
use App\Http\Controllers\StockMovementController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ZaloAuthController;
use Illuminate\Support\Facades\Broadcast;

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
    Route::get('/users/profile', [UserController::class, 'profile'])->name('users.profile');
    Route::patch('/users/profile', [UserController::class, 'updateProfile'])->name('users.updateProfile');

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

    // Voucher routes
    Route::resource('vouchers', VoucherController::class);

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
    Route::get('stock-movements/imports', [StockMovementController::class, 'imports'])->name('stock-movements.imports');
    Route::get('stock-movements/exports', [StockMovementController::class, 'exports'])->name('stock-movements.exports');

    // Notification routes
    Route::resource('notifications', NotificationController::class);

    // Mobile App routes
    Route::get('mobileapp/banners', [MobileAppController::class, 'banners'])->name('mobileapp.banners');
    Route::get('mobileapp/ai-config', [MobileAppController::class, 'index'])->name('mobileapp.ai-config');

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

    Route::post('/invoices/{invoice}/process-payment', [InvoiceController::class, 'processPayment'])
        ->name('invoices.process-payment');

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

    // Thêm route mới cho trang chi tiết lịch hẹn
    Route::get('/appointments/{id}', [AppointmentController::class, 'show'])->name('appointments.show');
});

require __DIR__ . '/auth.php';
