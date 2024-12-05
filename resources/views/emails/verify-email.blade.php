<!DOCTYPE html>
<html lang="{{ $lang ?? 'vi' }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.email_verification') }}</title>
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

        .button {
            display: inline-block;
            padding: 12px 24px;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin: 20px 0;
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

    <h2>{{ __('messages.hello') }}</h2>

    <p>{{ __('messages.thank_you') }}</p>

    <div style="text-align: center;">
        <a href="{{ $verificationUrl }}" class="button">
            {{ __('messages.verify_email') }}
        </a>
    </div>

    <p>{{ __('messages.no_action_required') }}</p>

    <p>{{ __('messages.trouble_clicking') }}</p>
    <p style="word-break: break-all;">{{ $verificationUrl }}</p>

    <div class="footer">
        <p>{{ __('messages.email_sent_from') }}</p>
        <p>{{ __('messages.ignore_email') }}</p>
    </div>
</body>

</html>