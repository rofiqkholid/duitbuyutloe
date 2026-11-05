<!DOCTYPE html>
<html lang="id" class="h-full overflow-hidden">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <link rel="icon" type="image/png" sizes="192x192" href="/favicon.png">
    <link rel="apple-touch-icon" href="/favicon.png">
    <title>Buat Password Baru - Duit Buyut Loe</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
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
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Atur Ulang Password</h1>
            <p class="text-xs text-slate-500 mt-1">Masukkan password baru yang gampang diingat tapi susah ditebak orang lain</p>
        </div>

        <!-- Card Form -->
        <div class="rounded-2xl bg-white border border-slate-200 p-6 sm:p-8 shadow-xs">
            <form action="{{ route('password.update') }}" method="POST" class="space-y-3.5 sm:space-y-4">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">

                <div>
                    <label for="email" class="block text-xs font-semibold text-slate-700 mb-1.5">
                        Alamat Email
                    </label>
                    <div class="relative">
                        <input type="email" id="email" name="email" value="{{ $email ?? old('email') }}" readonly class="w-full pl-10 pr-3.5 py-2.5 rounded-xl bg-slate-100 border border-slate-300 text-slate-500 text-sm font-normal cursor-not-allowed select-none focus:outline-none placeholder:text-slate-400">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <i data-lucide="mail" class="w-4 h-4"></i>
                        </div>
                    </div>
                    @error('email')
                        <p class="text-red-500 text-xs mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="block text-xs font-semibold text-slate-700 mb-1.5">
                        Password Baru
                    </label>
                    <div class="relative">
                        <input type="password" id="password" name="password" placeholder="Minimal 6 karakter" required autofocus class="w-full pl-10 pr-3.5 py-2.5 rounded-xl bg-white border border-slate-300 text-slate-900 text-sm font-normal focus:outline-none focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600 placeholder:text-slate-400">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <i data-lucide="lock" class="w-4 h-4"></i>
                        </div>
                    </div>
                    @error('password')
                        <p class="text-red-500 text-xs mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="block text-xs font-semibold text-slate-700 mb-1.5">
                        Konfirmasi Password Baru
                    </label>
                    <div class="relative">
                        <input type="password" id="password_confirmation" name="password_confirmation" placeholder="Ulangi password baru" required class="w-full pl-10 pr-3.5 py-2.5 rounded-xl bg-white border border-slate-300 text-slate-900 text-sm font-normal focus:outline-none focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600 placeholder:text-slate-400">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <i data-lucide="check-check" class="w-4 h-4"></i>
                        </div>
                    </div>
                </div>

                <button type="submit" class="w-full py-2.5 px-4 rounded-full font-bold text-sm btn-primary cursor-pointer">
                    Simpan & Ubah Password
                </button>

                <div class="text-center pt-2">
                    <a href="{{ route('login') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-600 hover:text-indigo-600 transition-colors">
                        <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
                        <span>Batal & Kembali ke Login</span>
                    </a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>

