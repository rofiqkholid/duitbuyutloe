<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <link rel="icon" type="image/png" sizes="192x192" href="/favicon.png">
    <link rel="apple-touch-icon" href="/favicon.png">
    <title>Login</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
</head>
<body class="bg-slate-50 text-slate-800 min-h-screen flex items-center justify-center p-4 font-sans antialiased bg-[radial-gradient(ellipse_80%_80%_at_50%_-20%,rgba(99,102,241,0.15),rgba(255,255,255,0))]">

    <div class="max-w-md w-full space-y-6">
        <!-- Logo & Title -->
        <div class="text-center">
            <img src="{{ asset('images/icon-circle.png') }}" 
                 alt="Logo Duit Buyut Loe" 
                 class="w-16 h-16 rounded-full border border-slate-200/80 shadow-md object-cover mx-auto mb-3 hover:scale-105 transition-transform duration-200">
            <h1 class="text-2xl font-semibold text-slate-900 tracking-tight">Duit Buyut Loe</h1>
            <p class="text-xs text-slate-500 mt-1">Masuk pake Nomor Induk Kewarasan (NIK), jangan pura-pura ga waras</p>
        </div>

        @if(session('success'))
            <div class="flex items-center gap-2 p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs">
                <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-600 flex-shrink-0"></i>
                <div class="font-medium">{{ session('success') }}</div>
            </div>
        @endif

        @if($errors->any())
            <div class="p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs">
                <div class="flex items-center gap-2 font-bold mb-1">
                    <i data-lucide="alert-circle" class="w-4 h-4 text-rose-600 flex-shrink-0"></i>
                    Gagal Masuk (Salah NIK atau Password lu amnesia)
                </div>
                <ul class="list-disc ml-5 space-y-0.5">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Card Login Form -->
        <div class="rounded-2xl bg-white border border-slate-200 p-6 sm:p-8">
            <form action="{{ route('login') }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label for="nik" class="block text-xs font-medium text-slate-700 mb-1.5">
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
                    <label for="password" class="block text-xs font-medium text-slate-700 mb-1.5">
                        Password
                    </label>
                    <div class="relative">
                        <input type="password" id="password" name="password" placeholder="Password rahasia lu" required class="w-full pl-10 pr-3.5 py-2.5 rounded-xl bg-white border border-slate-300 text-slate-900 text-sm font-normal focus:outline-none focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600 placeholder:text-slate-400">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <i data-lucide="lock" class="w-4 h-4"></i>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-between text-xs pt-1">
                    <label class="flex items-center gap-2 cursor-pointer text-slate-600">
                        <input type="checkbox" name="remember" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                        Inget gue (Biar ga bolak-balik login)
                    </label>
                </div>

                @if(config('services.turnstile.site_key'))
                    <div class="cf-turnstile my-2 flex justify-center" data-sitekey="{{ config('services.turnstile.site_key') }}" data-theme="light"></div>
                @endif

                @php 
                    $lockoutSec = max((int)session('lockout_seconds', 0), (int)($lockoutSeconds ?? 0)); 
                @endphp
                <button type="submit" id="submitLoginBtn" class="w-full py-2.5 px-4 rounded-full font-bold text-sm btn-primary cursor-pointer disabled:opacity-60 disabled:cursor-not-allowed" {{ $lockoutSec > 0 ? 'disabled' : '' }}>
                    <span id="btnSubmitText">Masuk & Hadapi Kenyataan Tagihan</span>
                </button>

                <div class="flex items-center my-4">
                    <div class="flex-grow border-t border-slate-200"></div>
                    <span class="flex-shrink mx-3 text-xs text-slate-400">atau</span>
                    <div class="flex-grow border-t border-slate-200"></div>
                </div>

                <a href="{{ route('dashboard') }}" class="w-full py-2.5 px-4 rounded-full font-semibold text-xs text-slate-700 bg-slate-100 hover:bg-slate-200 hover:text-slate-900 transition-all flex items-center justify-center gap-2">
                    <i data-lucide="layout-dashboard" class="w-4 h-4 text-slate-600"></i>
                    <span>Cuma Mau Kepoin Bon Orang (Masuk Mode Tamu)</span>
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
