<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Duit Buyut Loe</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 text-slate-800 min-h-screen flex items-center justify-center p-4 font-sans antialiased bg-[radial-gradient(ellipse_80%_80%_at_50%_-20%,rgba(99,102,241,0.15),rgba(255,255,255,0))]">

    <div class="max-w-md w-full space-y-6">
        <!-- Logo & Title -->
        <div class="text-center">
            <div class="w-14 h-14 rounded-2xl btn-primary mx-auto flex items-center justify-center mb-3">
                <i data-lucide="home" class="w-7 h-7 text-white"></i>
            </div>
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

                <button type="submit" class="w-full py-2.5 px-4 rounded-full font-bold text-sm btn-primary">
                    Masuk & Hadapi Kenyataan Tagihan
                </button>
            </form>
        </div>

    </div>

</body>
</html>
