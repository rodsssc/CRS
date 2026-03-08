<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ config('app.name', 'Laravel') }}</title>
        <link rel="stylesheet" href="{{ asset('assets/css/auth/auth.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/css/auth/login.css') }}">
        <link href="{{ asset('assets/css/app.css') }}" rel="stylesheet">
    </head>
    <body>
        <div class="main-container">
            <div class="main-content">
                {{ $slot }}
            </div>
        </div>
    </body>
    <script>
    function togglePassword(fieldId, icon) {
        const input = document.getElementById(fieldId);
        const isHidden = input.type === 'password';
        input.type = isHidden ? 'text' : 'password';
        icon.style.color = isHidden ? '#4f46e5' : '#9ca3af';
    }
</script>
</html>