<!DOCTYPE html>
<html lang="id" class="h-full overflow-hidden">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <link rel="icon" type="image/png" sizes="192x192" href="/favicon.png">
    <link rel="apple-touch-icon" href="/favicon.png">
    <title>Lupa Password - Duit Buyut Loe</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
</head>
<body class="bg-slate-50 text-slate-800 h-screen h-dvh flex items-center justify-center p-4 pb-14 sm:pb-20 font-sans antialiased overflow-hidden overscroll-none bg-[radial-gradient(ellipse_80%_80%_at_50%_-20%,rgba(99,102,241,0.15),rgba(255,255,255,0))]">

    @include('partials.alert')

    <div class="max-w-md w-full space-y-4 sm:space-y-6 -translate-y-8 sm:-translate-y-12">
        <!-- Logo & Title -->
        <div class="text-center">
            <a href="{{ route('login') }}" class="inline-block">
                <img src="{{ asset('images/icon-circle.png') }}" 
                     alt="Logo Duit Buyut Loe" 
                     class="w-20 h-20 sm:w-24 sm:h-24 rounded-full border border-slate-200/80 shadow-md object-cover mx-auto mb-2.5 hover:scale-105 transition-transform duration-200">
            </a>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Lupa Password?</h1>
            <p class="text-xs text-slate-500 mt-1">Masukkan Nomor Induk Kewarasan (NIK) lu, kami kirim link reset ke email terdaftar</p>
        </div>

        <!-- Card Form -->
        <div class="rounded-2xl bg-white border border-slate-200 p-6 sm:p-8 shadow-xs">
            @if (session('status'))
                <div class="mb-4 p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-xs text-emerald-800 flex items-start gap-2 leading-relaxed">
                    <i data-lucide="check-circle" class="w-4 h-4 text-emerald-600 shrink-0 mt-0.5"></i>
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            <form action="{{ route('password.email') }}" method="POST" class="space-y-3.5 sm:space-y-4">
                @csrf

                <div>
                    <label for="nik" class="block text-xs font-semibold text-slate-700 mb-1.5">
                        Nomor Induk Kewarasan (NIK)
                    </label>
                    <div class="relative">
                        <input type="text" id="nik" name="nik" value="{{ old('nik') }}" placeholder="Ketik NIK lu di sini" required autofocus class="w-full pl-10 pr-3.5 py-2.5 rounded-xl bg-white border border-slate-300 text-slate-900 text-sm font-normal focus:outline-none focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600 placeholder:text-slate-400">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <i data-lucide="user" class="w-4 h-4"></i>
                        </div>
                    </div>
                    @error('nik')
                        <p class="text-red-500 text-xs mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                @if(config('services.turnstile.site_key'))
                    <div class="cf-turnstile my-1.5 sm:my-2 flex justify-center" data-sitekey="{{ config('services.turnstile.site_key') }}" data-theme="light"></div>
                @endif

                <button type="submit" class="w-full py-2.5 px-4 rounded-full font-bold text-sm btn-primary cursor-pointer">
                    Kirim Link Reset Password
                </button>

                <div class="text-center pt-2">
                    <a href="{{ route('login') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-600 hover:text-indigo-600 transition-colors">
                        <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
                        <span>Kembali ke Halaman Login</span>
                    </a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
