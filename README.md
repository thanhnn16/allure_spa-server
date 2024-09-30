## Cần cài đặt trước khi chạy project

- [PHP (>=8.2)](https://www.php.net/manual/en/install.php): Tải về và giải nén (thư mục không chứa dấu cách, không chứa ký tự đặc biệt, nên đặt trong ổ C, ví dụ: `C:\php`)
- [Composer](https://getcomposer.org/download/): Tải về và cài đặt, chọn đường dẫn đến file `php.exe` trong thư mục PHP đã cài đặt ở trên (ví dụ: `C:\php\php.exe`), nhớ tích vào ô `Add PHP to PATH`
- [Nodejs](https://nodejs.org/en/download/): khuyến khích cài bản `LTS` (Long Term Support)
- [MySQL](https://dev.mysql.com/downloads/): Tải về và cài đặt, nhớ lưu username và password để kết nối với database. Khuyến khích tải bản `LTS` (Long Term Support)
- [Git](https://git-scm.com/downloads): Tải về và cài đặt
- [DataGrip (Khuyên dùng)](https://www.jetbrains.com/datagrip/download/): Tải về và cài đặt, dùng để kết nối với database MySQL.
- [MySQL Workbench (nếu không dùng DataGrip)](https://dev.mysql.com/downloads/workbench/): Tải về và cài đặt, dùng để kết nối với database MySQL.
- [Postman](https://www.postman.com/downloads/): Tải về và cài đặt, dùng để test API. Gửi email đăng ký tài khoản cho `Thành` để thêm vào team/workspace.

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

Chạy file tất cả các file sql trong thư mục `database/sql` để tạo database, dữ liệu mẫu và trigger.
Thứ tự các file sql để chạy:
1. allure_dev.sql
2. allure_dev_data.sql
3. allure_dev_trigger.sql
4. allure_dev_view.sql

```bash
mysql -u root -p < database/sql/allure_dev.sql
```

```bash
mysql -u root -p < database/sql/allure_dev_data.sql
```

```bash
mysql -u root -p < database/sql/allure_dev_trigger.sql
```

```bash
mysql -u root -p < database/sql/allure_dev_view.sql
```

Chạy lệnh `dump-autoload` để tạo file autoload.php (Công dụng: composer dump-autoload là một lệnh quan trọng để quản lý autoloading trong project PHP sử dụng Composer. Nó đảm bảo PHP có thể tìm và load các class một cách tự động)

```bash
composer dump-autoload
```

Chạy lệnh `db:seed` để tạo dữ liệu mẫu cho database (ở đây sẽ tạo ra 2 user mới, xem thêm trong file `database/seeders/DatabaseSeeder.php`)

```bash
php artisan db:seed
```

### 3. Tạo file .env (copy từ file .env.example)

```bash
cp .env.example .env
```

Trong file `.env` cần chỉnh sửa các thông số sau:

- DB_USERNAME=`username của MySQL trên máy của bạn` (mặc định là root)
- DB_PASSWORD=`password của MySQL trên máy của bạn`

**_Ví dụ:_**

```
DB_USERNAME=root
DB_PASSWORD=123456
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

### 7. Một số lệnh thường dùng

**_Lệnh tạo controller:_**

```bash
php artisan make:controller `TênController`
```

**_Lệnh tạo model:_**

```bash
php artisan make:model `TênModel`
```

```bash
php artisan ziggy:generate
```

```bash
php artisan route:cache
```

```bash
php artisan route:clear
```

```bash
php artisan config:cache
```

```bash
php artisan config:clear
```


## Đọc thêm Document (Nên đọc để hiểu rõ hơn về các công nghệ sử dụng)

- [Laravel](https://laravel.com/docs)
- [Vuejs](https://vuejs.org/guide/introduction.html)
- [Tailwindcss](https://tailwindcss.com/docs)
- [Inertiajs](https://inertiajs.com/)
- [Sanctum](https://laravel.com/docs/11.x/sanctum#main-content)
- [Laravel Tinker](https://laravel.com/docs/11.x/artisan#tinker)
- [Ziggy](https://github.com/tighten/ziggy)
- [Elqouent](https://laravel.com/docs/11.x/eloquent)
- [L5-Swagger](https://github.com/DarkaOnLine/L5-Swagger/wiki)
