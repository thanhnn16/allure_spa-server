<!DOCTYPE html>
<html lang="{{ $lang ?? 'vi' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.reset_password_success_title') }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            text-align: center;
        }

        .success-icon {
            color: #4CAF50;
            font-size: 64px;
            margin: 20px 0;
        }

        .message {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
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
        }
    </style>
</head>
<body>
    <div class="logo">
        <img src="https://allurespa.com.vn/wp-content/uploads/2024/08/logo_homepage-e1723436204113.png" alt="Logo" height="50">
    </div>

    <div class="success-icon">✓</div>

    <h1>{{ __('messages.reset_password_success_title') }}</h1>
    
    <div class="message">
        <p>{{ __('messages.reset_password_success_message') }}</p>
    </div>

    <a href="allurespa:" class="button">
        {{ __('messages.back_to_app') }}
    </a>
</body>
</html> 