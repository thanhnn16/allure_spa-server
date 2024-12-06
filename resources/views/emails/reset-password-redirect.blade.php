<!DOCTYPE html>
<html>

<head>
    <title>{{ __('messages.reset_password') }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script>
        function openApp() {
            // Tạo deeplink URL
            const deeplinkUrl = "allurespa://reset-password?" + window.location.search;
            // Tạo fallback URL (URL hiện tại)
            const fallbackUrl = window.location.href.replace('/redirect/', '/web/');

            // Thử mở app
            window.location.href = deeplinkUrl;

            // Đợi một chút để kiểm tra xem app có mở không
            setTimeout(function() {
                // Nếu vẫn ở trang này, chuyển đến trang web
                window.location.href = fallbackUrl;
            }, 1000);
        }

        // Tự động chạy khi trang load
        window.onload = openApp;
    </script>
</head>

<body>
    <div style="text-align: center; padding: 20px;">
        <h2>{{ __('messages.reset_password') }}</h2>
        <p>{{ __('messages.redirecting') }}</p>
    </div>
</body>

</html>