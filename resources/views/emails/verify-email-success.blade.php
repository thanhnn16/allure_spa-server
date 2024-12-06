<!DOCTYPE html>
<html lang="{{ $lang ?? 'vi' }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.email_verification_success') }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }

        .logo {
            text-align: center;
            margin-bottom: 30px;
        }

        .success-icon {
            text-align: center;
            font-size: 48px;
            color: #4CAF50;
            margin: 20px 0;
        }

        .message-box {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            text-align: center;
        }

        .footer {
            margin-top: 30px;
            font-size: 12px;
            color: #666;
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="logo">
        <img src="https://allurespa.com.vn/wp-content/uploads/2024/08/logo_homepage-e1723436204113.png" alt="Logo" height="50">
    </div>

    <div class="success-icon">
        ✓
    </div>

    <div class="message-box">
        <h2>{{ __('messages.verification_successful') }}</h2>
        <p>{{ __('messages.thank_you_for_verifying') }}</p>
        <p>{{ __('messages.account_ready') }}</p>
    </div>

    <div class="footer">
        <p>{{ __('messages.email_sent_from') }}</p>
    </div>
</body>

</html> 