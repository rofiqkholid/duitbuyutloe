<!-- PWA / Add to Home Screen Prompt Modal & Banner -->
<div id="pwaInstallPrompt" class="fixed bottom-4 left-4 right-4 sm:left-auto sm:right-6 sm:bottom-6 sm:max-w-md z-50 transform translate-y-32 opacity-0 pointer-events-none transition-all duration-300 ease-out">
    <div class="bg-white/95 backdrop-blur-xl border border-slate-200/90 rounded-2xl p-4 sm:p-5 shadow-2xl shadow-indigo-500/15">
        <div class="flex items-start gap-3.5">
            <div class="w-12 h-12 rounded-xl btn-primary flex items-center justify-center flex-shrink-0 shadow-md shadow-purple-500/20">
                <i data-lucide="smartphone" class="w-6 h-6 text-white"></i>
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between gap-2">
                    <h4 class="text-sm font-bold text-slate-900 leading-tight">Pasang di Layar Utama?</h4>
                    <button type="button" onclick="dismissPwaPrompt()" class="text-slate-400 hover:text-slate-600 p-1 -mr-1 rounded-lg hover:bg-slate-100 transition-colors">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>
                <p class="text-xs text-slate-600 mt-1 leading-relaxed">
                    Simpan shortcut aplikasi di launcher HP Anda agar bisa dibuka cepat layaknya aplikasi native tanpa perlu ketik link lagi.
                </p>

                <!-- Petunjuk untuk iOS Safari jika prompt otomatis tidak didukung -->
                <div id="iosInstallGuide" class="hidden mt-2 p-2.5 rounded-xl bg-slate-50 border border-slate-200 text-[11px] text-slate-600">
                    <p class="font-semibold text-slate-800 mb-1 flex items-center gap-1.5">
                        <i data-lucide="share" class="w-3.5 h-3.5 text-indigo-600"></i> Cara pasang di iPhone/iPad:
                    </p>
                    <ol class="list-decimal list-inside space-y-0.5">
                        <li>Tekan tombol <strong>Bagikan (Share)</strong> di Safari.</li>
                        <li>Pilih <strong>"Tambah ke Layar Utama" (Add to Home Screen)</strong>.</li>
                    </ol>
                </div>

                <div class="flex items-center gap-2 mt-3.5">
                    <button type="button" id="btnInstallPwa" onclick="installPwaApp()" class="btn-primary text-xs font-semibold px-4 py-2 rounded-xl flex items-center gap-1.5 cursor-pointer shadow-sm">
                        <i data-lucide="download" class="w-3.5 h-3.5"></i>
                        <span>Ya, Pasang Sekarang</span>
                    </button>
                    <button type="button" onclick="dismissPwaPrompt()" class="text-xs font-medium text-slate-600 hover:text-slate-900 px-3 py-2 rounded-xl hover:bg-slate-100 transition-colors cursor-pointer">
                        Nanti Saja
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let deferredPrompt = null;
    const isIos = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
    const isStandalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;

    // Registrasi Service Worker
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('/sw.js').catch(() => {});
        });
    }

    // Tangkap event bawaan browser Android/Chrome (beforeinstallprompt)
    window.addEventListener('beforeinstallprompt', (e) => {
        // Cegah mini-infobar default browser agar bisa pakai UI custom kita
        e.preventDefault();
        deferredPrompt = e;

        // Cek jika pengguna belum menolak prompt baru-baru ini
        if (!localStorage.getItem('pwa_prompt_dismissed') && !isStandalone) {
            showPwaPrompt();
        }
    });

    // Handle jika di iOS Safari (belum standalone)
    document.addEventListener('DOMContentLoaded', () => {
        if (isIos && !isStandalone && !localStorage.getItem('pwa_prompt_dismissed')) {
            const iosGuide = document.getElementById('iosInstallGuide');
            const btnInstall = document.getElementById('btnInstallPwa');
            if (iosGuide) iosGuide.classList.remove('hidden');
            if (btnInstall) btnInstall.classList.add('hidden');
            setTimeout(showPwaPrompt, 2000);
        }

        if (window.lucide && typeof window.lucide.createIcons === 'function') {
            window.lucide.createIcons();
        }
    });

    function showPwaPrompt() {
        const modal = document.getElementById('pwaInstallPrompt');
        if (!modal) return;
        modal.classList.remove('translate-y-32', 'opacity-0', 'pointer-events-none');
        modal.classList.add('translate-y-0', 'opacity-100');
        if (window.lucideIcons && window.createIcons) {
            window.createIcons({ icons: window.lucideIcons });
        }
    }

    function dismissPwaPrompt() {
        const modal = document.getElementById('pwaInstallPrompt');
        if (modal) {
            modal.classList.remove('translate-y-0', 'opacity-100');
            modal.classList.add('translate-y-32', 'opacity-0', 'pointer-events-none');
        }
        // Jangan tanya lagi selama 3 hari
        localStorage.setItem('pwa_prompt_dismissed', Date.now());
    }

    async function installPwaApp() {
        if (!deferredPrompt) {
            alert('Silakan gunakan menu browser Anda dan pilih "Tambahkan ke Layar Utama" (Add to Home Screen).');
            return;
        }

        // Tampilkan dialog install bawaan OS
        deferredPrompt.prompt();
        const choiceResult = await deferredPrompt.userChoice;

        if (choiceResult.outcome === 'accepted') {
            console.log('User menerima install ke launcher');
            dismissPwaPrompt();
        } else {
            console.log('User membatalkan');
        }
        deferredPrompt = null;
    }

    // Jika berhasil terinstall
    window.addEventListener('appinstalled', () => {
        dismissPwaPrompt();
        deferredPrompt = null;
    });
</script>
