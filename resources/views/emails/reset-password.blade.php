<!DOCTYPE html>
<html lang="{{ $lang ?? 'vi' }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.reset_password') }}</title>
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
            color: white !important;
            text-decoration: none;
            border-radius: 4px;
            margin: 20px 0;
            text-align: center;
        }

        .content {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .footer {
            margin-top: 30px;
            font-size: 12px;
            color: #666;
            text-align: center;
        }

        .link-container {
            margin: 20px 0;
            word-break: break-all;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 4px;
            border: 1px solid #ddd;
        }
    </style>
</head>

<body>
    <div class="logo">
        <img src="https://allurespa.com.vn/wp-content/uploads/2024/08/logo_homepage-e1723436204113.png" alt="Logo" height="50">
    </div>

    <div class="content">
        <h2>{{ __('messages.reset_password_greeting') }}</h2>

        <p>{{ __('messages.reset_password_reason') }}</p>

        <p>{{ __('messages.reset_password_action') }}</p>

        <div style="text-align: center;">
            <a href="{{ $resetUrl }}" class="button">
                {{ __('messages.reset_password_button') }}
            </a>
        </div>

        <p>{{ __('messages.reset_password_expire') }}</p>

        <p>{{ __('messages.trouble_clicking') }}</p>

        <p>{{ __('messages.or_use_token') }}</p>
        <div class="link-container" style="position: relative;">
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

        <p>{{ __('messages.or_click_link') }}</p>
        <div class="link-container" style="position: relative;">
            <code style="display: block; word-break: break-all;">{{ $resetUrl }}</code>
            <button onclick="copyResetLink()" style="
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

        <p>{{ __('messages.reset_password_ignore') }}</p>
    </div>

    <div class="footer">
        <p>{{ __('messages.email_sent_from') }}</p>
    </div>
</body>

<script>
    function copyToken() {
        const tokenValue = '{{ $token }}';
        navigator.clipboard.writeText(tokenValue)
            .then(() => {
                alert('{{ __("messages.token_copied") }}');
            })
            .catch((err) => {
                console.error('Failed to copy token:', err);
            });
    }

    function copyResetLink() {
        const resetLink = '{{ $resetUrl }}';
        navigator.clipboard.writeText(resetLink)
            .then(() => {
                alert('{{ __("messages.link_copied") }}');
            })
            .catch((err) => {
                console.error('Failed to copy link:', err);
            });
    }
</script>

</html>