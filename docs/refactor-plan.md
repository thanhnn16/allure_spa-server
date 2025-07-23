# Kế Hoạch Refactor - Allure SPA Server

## Tổng Quan
Kế hoạch refactor này bao gồm:
1. Xóa toàn bộ features liên quan đến AI, Banner, Mobile App, Chat
2. Cập nhật lên Laravel 12 mới nhất
3. Chuyển đổi database từ MySQL/SQLite sang PostgreSQL
4. Thêm Redis cho caching và session

## 1. Xóa Features Không Cần Thiết

### 1.1 AI Features
**Files cần xóa:**
- `app/Models/AiChatConfig.php`
- `app/Services/AiChatConfigService.php`
- `app/Services/AiFunctionCallingService.php`
- `app/Http/Controllers/AiConfigController.php`
- `app/Http/Controllers/AiFunctionController.php`
- `database/seeders/AiChatConfigSeeder.php`
- `resources/js/Pages/AiConfig/AiConfigView.vue`
- Các build assets liên quan: `public/build/assets/AiConfigView-*.css`

**Routes cần xóa từ `routes/api.php`:**
```php
// AI Config routes
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
```

### 1.2 Banner Features
**Files cần xóa:**
- `app/Models/Banner.php`
- `app/Services/BannerService.php`
- `app/Http/Controllers/BannerController.php`
- `resources/js/Pages/MobileApp/Banners.vue`

**Routes cần xóa:**
```php
Route::get('banners', [BannerController::class, 'index'])->name('banners.index');
Route::post('banners', [BannerController::class, 'store']);
Route::put('banners/{banner}', [BannerController::class, 'update']);
Route::delete('banners/{banner}', [BannerController::class, 'destroy']);
```

### 1.3 Chat Features
**Files cần xóa:**
- `app/Models/Chat.php`
- `app/Models/ChatMessage.php`
- `app/Services/ChatService.php`
- `app/Services/ChatMessageService.php`
- `app/Http/Controllers/ChatController.php`
- `resources/js/Pages/Chats/ChatView.vue`
- Các build assets: `public/build/assets/ChatView-*.js`, `public/build/assets/ChatView-*.css`

**Routes cần xóa:**
```php
// Chat routes
Route::get('/chats', [ChatController::class, 'index']);
Route::post('/chats', [ChatController::class, 'store']);
Route::get('/chats/{chat}/messages', [ChatController::class, 'getMessages']);
Route::post('/messages', [ChatController::class, 'sendMessage']);
Route::post('/chats/{chat}/mark-as-read', [ChatController::class, 'markAsRead']);
```

### 1.4 Mobile App Features
**Files cần xóa:**
- `app/Models/FcmToken.php`
- `app/Services/FcmTokenService.php`
- `app/Services/FirebaseService.php`
- `app/Services/FirebaseAuthService.php`
- `app/Http/Controllers/FirebaseWebhookController.php`
- `public/firebase-messaging-sw.js`
- `resources/js/Stores/notificationStore.js`
- Các Firebase assets: `public/build/assets/firebase-*.js`

**Routes cần xóa:**
```php
Route::post('/fcm/token', [AuthController::class, 'storeFcmToken']);
Route::post('/auth/fcm-token', [AuthController::class, 'storeFcmToken']);
Route::post('firebase/webhook', [FirebaseWebhookController::class, 'handleMessage']);
```

**Dependencies cần xóa từ `composer.json`:**
- `kreait/laravel-firebase`

## 2. Cập Nhật Laravel 12

### 2.1 Cập nhật Dependencies
**Cập nhật `composer.json`:**
```json
{
    "require": {
        "php": "^8.3",
        "laravel/framework": "^12.0",
        "laravel/sanctum": "^4.0",
        "laravel/tinker": "^2.9",
        "darkaonline/l5-swagger": "^8.6",
        "inertiajs/inertia-laravel": "^2.0",
        "maatwebsite/excel": "^3.1",
        "payos/payos": "^1.0",
        "pusher/pusher-php-server": "^7.2",
        "tightenco/ziggy": "^2.0",
        "torann/geoip": "^3.0",
        "predis/predis": "^2.0"
    },
    "require-dev": {
        "fakerphp/faker": "^1.23",
        "laravel/breeze": "^2.1",
        "laravel/pint": "^1.13",
        "laravel/sail": "^1.26",
        "mockery/mockery": "^1.6",
        "nunomaduro/collision": "^8.0",
        "pestphp/pest": "^3.0",
        "pestphp/pest-plugin-laravel": "^3.0"
    }
}
```

### 2.2 Cập nhật Config Files
**Cập nhật `config/app.php`:**
- Kiểm tra và cập nhật các service providers mới
- Cập nhật timezone và locale settings

## 3. Chuyển Đổi Database sang PostgreSQL

### 3.1 Cập nhật Database Config
**Cập nhật `config/database.php`:**
```php
'default' => env('DB_CONNECTION', 'pgsql'),

'connections' => [
    'pgsql' => [
        'driver' => 'pgsql',
        'url' => env('DB_URL'),
        'host' => env('DB_HOST', '127.0.0.1'),
        'port' => env('DB_PORT', '5432'),
        'database' => env('DB_DATABASE', 'allure_spa'),
        'username' => env('DB_USERNAME', 'postgres'),
        'password' => env('DB_PASSWORD', ''),
        'charset' => env('DB_CHARSET', 'utf8'),
        'prefix' => '',
        'prefix_indexes' => true,
        'search_path' => 'public',
        'sslmode' => 'prefer',
    ],
    // Giữ lại các connections khác nếu cần
],
```

### 3.2 Cập nhật Environment Variables
**Cập nhật `.env.example`:**
```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=allure_spa
DB_USERNAME=postgres
DB_PASSWORD=

# Redis Configuration
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
REDIS_DB=0
REDIS_CACHE_DB=1

# Cache Configuration
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
```

### 3.3 Migration Adjustments
**Cần kiểm tra và điều chỉnh các migrations:**
- Thay đổi các data types không tương thích với PostgreSQL
- Cập nhật các indexes và constraints
- Xóa các migrations liên quan đến AI, Banner, Chat, FCM

## 4. Thêm Redis Support

### 4.1 Cập nhật Cache Config
**Cập nhật `config/cache.php`:**
```php
'default' => env('CACHE_DRIVER', 'redis'),

'stores' => [
    'redis' => [
        'driver' => 'redis',
        'connection' => 'cache',
        'lock_connection' => 'default',
    ],
],
```

### 4.2 Cập nhật Session Config
**Cập nhật `config/session.php`:**
```php
'driver' => env('SESSION_DRIVER', 'redis'),
'connection' => 'default',
```

### 4.3 Cập nhật Queue Config
**Cập nhật `config/queue.php`:**
```php
'default' => env('QUEUE_CONNECTION', 'redis'),
```

## 5. Cleanup và Optimization

### 5.1 Xóa Unused Imports
**Cần kiểm tra và xóa các imports không sử dụng trong:**
- Controllers
- Services
- Models
- Routes

### 5.2 Cập nhật Documentation
**Cập nhật các file documentation:**
- `README.md`
- API documentation
- Swagger annotations

### 5.3 Testing
**Cần test lại các chức năng:**
- Authentication
- Appointments
- Services
- Products
- Orders
- Invoices
- Ratings
- Notifications

## 6. Thứ Tự Thực Hiện

1. **Backup dữ liệu hiện tại**
2. **Xóa AI features** (models, controllers, services, routes)
3. **Xóa Banner features**
4. **Xóa Chat features**
5. **Xóa Mobile App features** (Firebase, FCM)
6. **Cập nhật Laravel 12**
7. **Setup PostgreSQL**
8. **Setup Redis**
9. **Migrate dữ liệu**
10. **Testing toàn bộ hệ thống**
11. **Cập nhật documentation**

## 7. Rủi Ro và Lưu Ý

### 7.1 Rủi Ro
- Mất dữ liệu khi migrate database
- Breaking changes trong Laravel 12
- Compatibility issues với third-party packages

### 7.2 Lưu Ý
- Backup toàn bộ database trước khi bắt đầu
- Test từng bước một cách cẩn thận
- Chuẩn bị rollback plan
- Cập nhật server requirements (PHP 8.3+, PostgreSQL, Redis)

## 8. Post-Refactor Tasks

1. **Performance Optimization**
   - Implement Redis caching cho các queries thường dùng
   - Optimize database indexes
   - Setup query monitoring

2. **Security Review**
   - Review authentication flows
   - Update security headers
   - Audit API endpoints

3. **Monitoring Setup**
   - Setup application monitoring
   - Database performance monitoring
   - Redis monitoring

4. **Documentation Update**
   - API documentation
   - Deployment guide
   - Development setup guide