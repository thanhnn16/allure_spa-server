<!DOCTYPE html>
<html>

<head>
    <title>{{ __('messages.reset_password') }}</title>
</head>

<body>
    <h2>{{ __('messages.reset_password_greeting') }}</h2>
    <p>{{ __('messages.reset_password_reason') }}</p>
    <p>{{ __('messages.reset_password_action') }}</p>
    <a href="{{ $resetUrl }}" style="background-color: #4CAF50; color: white; padding: 14px 20px; text-decoration: none; border-radius: 4px;">
        {{ __('messages.reset_password_button') }}
    </a>
    <p>{{ __('messages.reset_password_expire') }}</p>
    <p>{{ __('messages.reset_password_ignore') }}</p>
    <p>{{ __('messages.email_sent_from') }}</p>
</body>

</html>