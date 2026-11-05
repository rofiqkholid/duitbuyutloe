<!DOCTYPE html>
<html lang="id" class="h-full overflow-hidden">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <link rel="icon" type="image/png" sizes="192x192" href="/favicon.png">
    <link rel="apple-touch-icon" href="/favicon.png">
    <title>Login</title>
    <script>
        // Filter out internal Cloudflare Turnstile CSP & WebGPU noise from developer console
        (function() {
            const origError = console.error;
            const origWarn = console.warn;
            console.error = function(...args) {
                const msg = args.map(a => (typeof a === 'object' && a !== null ? (a.message || '') : String(a))).join(' ');
                if (msg.includes('Content Security Policy') && (msg.includes('challenges.cloudflare.com') || msg.includes('data:,'))) return;
                if (msg.includes('Refused to connect because it violates the document\'s Content Security Policy')) return;
                origError.apply(console, args);
            };
            console.warn = function(...args) {
                const msg = args.map(a => (typeof a === 'object' && a !== null ? (a.message || '') : String(a))).join(' ');
                if (msg.includes('powerPreference') || msg.includes('No available adapters')) return;
                origWarn.apply(console, args);
            };
        })();
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
</head>
<body class="bg-slate-50 text-slate-800 h-screen h-dvh flex items-center justify-center p-4 pb-14 sm:pb-20 font-sans antialiased overflow-hidden overscroll-none bg-[radial-gradient(ellipse_80%_80%_at_50%_-20%,rgba(99,102,241,0.15),rgba(255,255,255,0))]">

    @include('partials.alert')

    <div class="max-w-md w-full space-y-4 sm:space-y-6 -translate-y-8 sm:-translate-y-12">
        <!-- Logo & Title -->
        <div class="text-center">
            <img src="{{ asset('images/icon-circle.png') }}" 
                 alt="Logo Duit Buyut Loe" 
                 class="w-20 h-20 sm:w-24 sm:h-24 rounded-full border border-slate-200/80 shadow-md object-cover mx-auto mb-2.5 hover:scale-105 transition-transform duration-200">
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Duit Buyut Loe</h1>
            <p class="text-xs text-slate-500 mt-1">Masuk pake Nomor Induk Kewarasan (NIK), jangan pura-pura ga waras</p>
        </div>

        <!-- Card Login Form -->
        <div class="rounded-2xl bg-white border border-slate-200 p-6 sm:p-8 shadow-xs">
            <form action="{{ route('login') }}" method="POST" class="space-y-3.5 sm:space-y-4">
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
                </div>

                <div>
                    <label for="password" class="block text-xs font-semibold text-slate-700 mb-1.5">
                        Password
                    </label>
                    <div class="relative">
                        <input type="password" id="password" name="password" placeholder="Password rahasia lu" required class="w-full pl-10 pr-3.5 py-2.5 rounded-xl bg-white border border-slate-300 text-slate-900 text-sm font-normal focus:outline-none focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600 placeholder:text-slate-400">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <i data-lucide="lock" class="w-4 h-4"></i>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-between text-xs pt-0.5">
                    <label class="flex items-center gap-2 cursor-pointer text-slate-600">
                        <input type="checkbox" name="remember" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                        Inget gue
                    </label>
                    <a href="{{ route('password.request') }}" class="font-medium text-indigo-600 hover:text-indigo-800 transition-colors">
                        Lupa Password?
                    </a>
                </div>

                @if(config('services.turnstile.site_key'))
                    <div class="cf-turnstile my-1.5 sm:my-2 flex justify-center" data-sitekey="{{ config('services.turnstile.site_key') }}" data-theme="light"></div>
                @endif

                @php 
                    $lockoutSec = max((int)session('lockout_seconds', 0), (int)($lockoutSeconds ?? 0)); 
                @endphp
                <button type="submit" id="submitLoginBtn" class="w-full py-2.5 px-4 rounded-full font-bold text-sm btn-primary cursor-pointer disabled:opacity-60 disabled:cursor-not-allowed" {{ $lockoutSec > 0 ? 'disabled' : '' }}>
                    <span id="btnSubmitText">Masuk & Hadapi Kenyataan Tagihan</span>
                </button>

                <div class="flex items-center my-3 sm:my-4">
                    <div class="flex-grow border-t border-slate-200"></div>
                    <span class="flex-shrink mx-3 text-xs text-slate-400">atau</span>
                    <div class="flex-grow border-t border-slate-200"></div>
                </div>

                <a href="{{ route('dashboard') }}" class="w-full py-2.5 px-4 rounded-full font-semibold text-xs text-slate-700 bg-slate-100 hover:bg-slate-200 hover:text-slate-900 transition-all flex items-center justify-center gap-2">
                    <i data-lucide="layout-dashboard" class="w-4 h-4 text-slate-600"></i>
                    <span>Masuk Mode Tamu</span>
                </a>
            </form>
        </div>

    </div>

    @if($lockoutSec > 0)
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            let timeLeft = {{ $lockoutSec }};
            const btn = document.getElementById('submitLoginBtn');
            const btnText = document.getElementById('btnSubmitText');
            const nikInput = document.getElementById('nik');
            const passInput = document.getElementById('password');

            if (nikInput) nikInput.disabled = true;
            if (passInput) passInput.disabled = true;

            const updateButtonCountdown = () => {
                if (timeLeft > 0) {
                    btn.disabled = true;
                    btnText.textContent = `Tunggu ${timeLeft} detik lagi...`;
                    timeLeft--;
                } else {
                    clearInterval(countdownTimer);
                    btn.disabled = false;
                    btnText.textContent = 'Masuk & Hadapi Kenyataan Tagihan';
                    if (nikInput) {
                        nikInput.disabled = false;
                        nikInput.focus();
                    }
                    if (passInput) passInput.disabled = false;
                }
            };

            updateButtonCountdown();
            const countdownTimer = setInterval(updateButtonCountdown, 1000);
        });
    </script>
    @endif

</body>
</html>
