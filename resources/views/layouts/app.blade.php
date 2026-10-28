<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Iuran Kontrakan') - Split Bill & Manajemen Faktur</title>
    <!-- Tailwind CSS, FontAwesome & Outfit Font compiled via Vite (Tanpa CDN) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 text-slate-800 min-h-screen flex flex-col font-sans antialiased bg-[radial-gradient(ellipse_80%_80%_at_50%_-20%,rgba(99,102,241,0.12),rgba(255,255,255,0))]">
    
    <!-- Navbar Header (Light Mode) -->
    <header class="sticky top-0 z-50 backdrop-blur-xl bg-white/80 border-b border-slate-200 px-4 py-3 sm:px-6">
        <div class="max-w-7xl mx-auto flex flex-row items-center justify-between gap-3">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 sm:gap-3 group">
                <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl btn-primary flex items-center justify-center group-hover:scale-105 transition-transform duration-200">
                    <i data-lucide="home" class="w-4 h-4 sm:w-5 sm:h-5 text-white"></i>
                </div>
                <div>
                    <h1 class="text-base sm:text-lg font-bold tracking-tight text-slate-900">
                        Iuran Kontrakan
                    </h1>
                </div>
            </a>

            <div class="flex items-center gap-2 sm:gap-3">
                @auth
                    <!-- User Profile Dropdown -->
                    <div class="relative">
                        <button type="button" id="userMenuButton" onclick="document.getElementById('userDropdownMenu').classList.toggle('hidden')" class="flex items-center gap-2 sm:gap-2.5 bg-white border-primary-gradient rounded-full py-1 sm:py-1.5 pl-1 sm:pl-1.5 pr-2.5 sm:pr-3.5 transition-all hover:scale-[1.02] cursor-pointer text-left">
                            <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-full btn-primary font-bold text-xs flex items-center justify-center flex-shrink-0">
                                {{ substr(Auth::user()->name ?? 'U', 0, 1) }}
                            </div>
                            <div class="text-left">
                                <div class="text-xs font-bold text-slate-900 leading-tight">{{ Auth::user()->name }}</div>
                                <div class="text-[10px] text-slate-500 font-medium leading-tight hidden sm:block">{{ Auth::user()->nik }}</div>
                            </div>
                            <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-slate-400"></i>
                        </button>

                        <!-- Dropdown Menu -->
                        <div id="userDropdownMenu" class="hidden absolute right-0 mt-2 w-56 rounded-2xl bg-white border border-slate-200 py-2 z-50 animate-in fade-in slide-in-from-top-1">
                            <!-- User info header -->
                            <div class="px-4 py-2.5 border-b border-slate-100">
                                <div class="font-bold text-xs text-slate-900">{{ Auth::user()->name }}</div>
                                <div class="text-[11px] text-slate-500 font-medium mt-0.5">{{ Auth::user()->nik }}</div>
                                @if(Auth::user()->email)
                                    <div class="text-[11px] text-slate-400 truncate mt-0.5">{{ Auth::user()->email }}</div>
                                @endif
                            </div>

                            <!-- Menu Items -->
                            <div class="py-1">
                                <a href="{{ route('profile.show') }}" class="flex items-center gap-2.5 px-4 py-2 text-xs font-medium text-slate-700 hover:bg-slate-50 hover:text-slate-900 transition-colors">
                                    <i data-lucide="user" class="w-4 h-4 text-slate-400"></i>
                                    Profil & Rekening
                                </a>
                                <a href="{{ route('bills.index') }}" class="flex items-center gap-2.5 px-4 py-2 text-xs font-medium text-slate-700 hover:bg-slate-50 hover:text-slate-900 transition-colors">
                                    <i data-lucide="receipt" class="w-4 h-4 text-slate-400"></i>
                                    Semua Faktur
                                </a>
                            </div>

                            <!-- Logout Item -->
                            <div class="pt-1 border-t border-slate-100">
                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="w-full flex items-center gap-2.5 px-4 py-2 text-xs font-bold text-rose-600 hover:bg-rose-50 transition-colors text-left cursor-pointer">
                                        <i data-lucide="log-out" class="w-4 h-4 text-rose-500"></i>
                                        Logout
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="inline-flex items-center gap-2 px-4 py-2 text-xs sm:text-sm font-semibold rounded-full btn-primary">
                        <i data-lucide="log-in" class="w-4 h-4"></i>
                        Login untuk kewarasan
                    </a>
                @endauth
            </div>
        </div>
    </header>

    <!-- Content Container -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 py-6 sm:py-8 flex-1 w-full">
        @include('partials.alert')

        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="border-t border-slate-200 bg-white py-6 text-center text-xs text-slate-500">
        &copy; {{ date('Y') }} Sistem Iuran Kontrakan &bull; Login Nomor Induk Kewarasan (NIK)
    </footer>

    @include('components.confirm-modal')

    @yield('scripts')
    <script>
        document.addEventListener('click', function(event) {
            const dropdown = document.getElementById('userDropdownMenu');
            const button = document.getElementById('userMenuButton');
            if (dropdown && button && !button.contains(event.target) && !dropdown.contains(event.target)) {
                dropdown.classList.add('hidden');
            }
        });
    </script>
</body>
</html>
