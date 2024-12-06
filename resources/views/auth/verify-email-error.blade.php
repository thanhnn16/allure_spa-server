<!DOCTYPE html>
<html lang="{{ $lang ?? 'vi' }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.verification_failed') }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            background-color: #f8f9fa;
        }

        .container {
            max-width: 600px;
            padding: 40px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .logo {
            margin-bottom: 30px;
        }

        .error-icon {
            font-size: 64px;
            color: #dc3545;
            margin: 20px 0;
        }

        .button {
            display: inline-block;
            padding: 12px 24px;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin: 20px 0;
            border: none;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
        }

        .button:hover {
            background-color: #45a049;
        }

        .message {
            margin: 20px 0;
            font-size: 18px;
            color: #dc3545;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="logo">
            <img src="https://allurespa.com.vn/wp-content/uploads/2024/08/logo_homepage-e1723436204113.png" alt="Logo" height="50">
        </div>

        <div class="error-icon">
            ✕
        </div>

        <h2>{{ __('messages.verification_failed') }}</h2>

        <div class="message">
            <p>{{ __('messages.invalid_or_expired_token') }}</p>
        </div>

        <a href="allurespa:email-verify/failed" class="button">
            {{ __('messages.return_to_app') }}
        </a>
    </div>

    <script>
        // Tự động chuyển hướng sau 3 giây
        setTimeout(function() {
            window.location.href = 'allurespa:email-verify/failed';
        }, 3000);
    </script>
</body>

</html>