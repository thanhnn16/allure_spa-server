<?php

use App\Http\Controllers\AiChatConfigController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProductController;
use App\Models\ProductCategory;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\VoucherController;
use App\Http\Controllers\TreatmentController;
use App\Http\Controllers\TreatmentCategoryController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\BannerController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\RatingController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\StockMovementController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ReportController;

Route::get('/', function () {
    if (Auth::check()) {
        return redirect('/dashboard');
    }
    return redirect()->route('login');
})->name('index');

Route::get('/dashboard', function () {
    return Inertia::render('HomeView', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'user' => Auth::user(),
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    // User routes
    Route::resource('users', UserController::class);
    Route::post('users/import', [UserController::class, 'import'])->name('users.import');
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

    // Voucher routes
    Route::resource('vouchers', VoucherController::class);

    // Treatment routes
    Route::resource('treatments', TreatmentController::class);

    // Treatment Category routes
    Route::resource('treatment-categories', TreatmentCategoryController::class);

    // Appointment routes
    Route::resource('appointments', AppointmentController::class);

    // Favorite routes
    Route::resource('favorites', FavoriteController::class);

    // Cart routes
    Route::resource('carts', CartController::class);

    // Rating routes
    Route::resource('ratings', RatingController::class);

    // Staff routes
    Route::resource('staff', StaffController::class);
    Route::get('staff/salary', [StaffController::class, 'salary'])->name('staff.salary');


    // Stock Movement routes
    Route::resource('stock-movements', StockMovementController::class);
    Route::get('stock-movements/imports', [StockMovementController::class, 'imports'])->name('stock-movements.imports');
    Route::get('stock-movements/exports', [StockMovementController::class, 'exports'])->name('stock-movements.exports');

    // Notification routes
    Route::resource('notifications', NotificationController::class);

    // Mobile App routes
    Route::get('mobileapp/chat', [ChatController::class, 'chat'])->name('mobileapp.chat');
    Route::get('mobileapp/banners', [BannerController::class, 'banners'])->name('mobileapp.banners');
    Route::get('mobileapp/ai-config', [AiChatConfigController::class, 'index'])->name('mobileapp.ai-config');

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

});

require __DIR__ . '/auth.php';
