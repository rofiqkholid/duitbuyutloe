@if(session('success') || session('error') || $errors->any())
<div id="toastNotification" class="fixed top-5 left-1/2 -translate-x-1/2 z-[100] max-w-md w-[92%] sm:w-auto transition-all duration-500 ease-out -translate-y-24 opacity-0 pointer-events-none">
    @if(session('success'))
        <div class="flex items-center gap-3 px-4 py-3 rounded-2xl bg-white/95 backdrop-blur-md border border-emerald-200 text-slate-800 shadow-xl shadow-emerald-500/10 pointer-events-auto">
            <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center flex-shrink-0">
                <i class="fa-solid fa-circle-check text-base fa-beat" style="--fa-animation-duration: 1.2s; --fa-beat-scale: 1.18;"></i>
            </div>
            <div class="text-xs font-semibold text-slate-800 pr-2">
                {{ session('success') }}
            </div>
            <button type="button" onclick="hideToastNotification()" class="text-slate-400 hover:text-slate-600 p-1 ml-auto">
                <i class="fa-solid fa-xmark text-xs"></i>
            </button>
        </div>
    @elseif(session('error'))
        <div class="flex items-center gap-3 px-4 py-3 rounded-2xl bg-white/95 backdrop-blur-md border border-rose-200 text-slate-800 shadow-xl shadow-rose-500/10 pointer-events-auto">
            <div class="w-8 h-8 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center flex-shrink-0">
                <i class="fa-solid fa-circle-xmark text-sm"></i>
            </div>
            <div class="text-xs font-semibold text-slate-800 pr-2">
                {{ session('error') }}
            </div>
            <button type="button" onclick="hideToastNotification()" class="text-slate-400 hover:text-slate-600 p-1 ml-auto">
                <i class="fa-solid fa-xmark text-xs"></i>
            </button>
        </div>
    @elseif($errors->any())
        <div class="flex items-start gap-3 px-4 py-3 rounded-2xl bg-white/95 backdrop-blur-md border border-rose-200 text-slate-800 shadow-xl shadow-rose-500/10 pointer-events-auto">
            <div class="w-8 h-8 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center flex-shrink-0 mt-0.5">
                <i class="fa-solid fa-triangle-exclamation text-sm"></i>
            </div>
            <div class="text-xs font-semibold text-slate-800 pr-2">
                <div class="font-bold text-rose-700 mb-1">Periksa kembali data Anda:</div>
                <ul class="list-disc list-inside space-y-0.5 text-slate-600 font-normal">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            <button type="button" onclick="hideToastNotification()" class="text-slate-400 hover:text-slate-600 p-1 ml-auto">
                <i class="fa-solid fa-xmark text-xs"></i>
            </button>
        </div>
    @endif
</div>

<script>
    function showToastNotification() {
        const toast = document.getElementById('toastNotification');
        if (!toast) return;

        // Animate slide down from top
        requestAnimationFrame(() => {
            toast.classList.remove('-translate-y-24', 'opacity-0', 'pointer-events-none');
            toast.classList.add('translate-y-0', 'opacity-100');
        });

        // Auto hide after 4 seconds
        setTimeout(() => {
            hideToastNotification();
        }, 4000);
    }

    function hideToastNotification() {
        const toast = document.getElementById('toastNotification');
        if (!toast) return;

        // Animate slide back up
        toast.classList.remove('translate-y-0', 'opacity-100');
        toast.classList.add('-translate-y-24', 'opacity-0', 'pointer-events-none');
    }

    document.addEventListener('DOMContentLoaded', showToastNotification);
</script>
@endif
