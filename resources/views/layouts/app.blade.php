<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#7c3aed">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="Duit Buyut Loe">
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <link rel="icon" type="image/png" sizes="192x192" href="/favicon.png">
    <link rel="manifest" href="/manifest.json">
    <link rel="apple-touch-icon" href="/favicon.png">
    <title>@yield('title', 'Duit Buyut Loe')</title>
    <!-- Tailwind CSS, FontAwesome & Outfit Font compiled via Vite (Tanpa CDN) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="bg-slate-50 text-slate-800 min-h-screen flex flex-col font-sans antialiased bg-[radial-gradient(ellipse_80%_80%_at_50%_-20%,rgba(99,102,241,0.12),rgba(255,255,255,0))]">
    @yield('content')

    @yield('scripts')
    @stack('scripts')
</body>
</html>
