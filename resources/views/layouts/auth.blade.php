<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/webp" href="{{ asset('images/logo_nu.webp') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/logo_nu.webp') }}">
    <title>@yield('title', 'Login') | {{ \App\Models\Setting::get('site_name', 'LAWNU JATIM') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 min-h-screen flex flex-col items-center justify-center p-4">
    @yield('content')

    <p class="text-center text-sm text-gray-500 mt-4">
        <a href="/" class="hover:text-emerald-600">← Kembali ke Website</a>
    </p>
</body>
</html>
