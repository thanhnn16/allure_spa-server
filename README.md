## About Laravel

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Cần cài đặt trước khi chạy project
- [PHP (>=8.2)](https://www.php.net/manual/en/install.php)
- [Composer](https://getcomposer.org/download/)
- [Nodejs](https://nodejs.org/en/download/)
- [MySQL](https://dev.mysql.com/downloads/)

## Hướng dẫn dùng Laravel + Vuejs + Tailwindcss + Inertiajs

### 1. Clone project

```bash
git clone https://github.com/thanhnn16/allure_spa-server.git
```

### 2. Cài đặt các thư viện cần thiết và database mẫu

```bash
composer install
```
```bash
npm install
```

Chạy file `allure_dev.sql` trong thư mục gốc để tạo database mẫu.

### 3. Tạo file .env (copy từ file .env.example)

```bash
cp .env.example .env
```

### 4. Tạo key cho project

```bash
php artisan key:generate
```

### 5. Chạy dev vite, để compile Vuejs và Tailwindcss
```bash
npm run dev
```

### 6. Chạy project (serve project)
Có thể chạy theo IP Wifi (để kết nối với điện thoại chung mạng) hoặc localhost

Nếu chạy theo IP Wifi
```bash
php artisan serve --host=`Địa chỉ IP Wifi`
```

Nếu chạy theo localhost
```bash
php artisan serve
```

## Đọc thêm Document
- [Laravel](https://laravel.com/docs)
- [Vuejs](https://vuejs.org/guide/introduction.html)
- [Tailwindcss](https://tailwindcss.com/docs)
- [Inertiajs](https://inertiajs.com/)
