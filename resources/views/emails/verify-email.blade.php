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

    <div style="margin: 20px 0; text-align: center;">
        <p>{{ __('messages.or_use_token') }}</p>
        <div style="
            background-color: #f8f9fa;
            padding: 10px;
            border-radius: 4px;
            border: 1px solid #ddd;
            margin: 10px 0;
            position: relative;
        ">
            <code style="display: block; word-break: break-all;">{{ $token }}</code>
            <button onclick="copyToken()" style="
                position: absolute;
                right: 10px;
                top: 50%;
                transform: translateY(-50%);
                background: #4CAF50;
                border: none;
                color: white;
                padding: 5px 10px;
                border-radius: 4px;
                cursor: pointer;
            ">
                {{ __('messages.copy') }}
            </button>
        </div>
    </div>

    <p>{{ __('messages.no_action_required') }}</p>

    <p>{{ __('messages.trouble_clicking') }}</p>
    <p style="word-break: break-all;">{{ $verificationUrl }}</p>

    <div class="footer">
        <p>{{ __('messages.email_sent_from') }}</p>
        <p>{{ __('messages.ignore_email') }}</p>
    </div>
</body>

<script>
function copyToken() {
    const token = '{{ $token }}';
    navigator.clipboard.writeText(token);
    alert('{{ __("messages.token_copied") }}');
}
</script>

</html>