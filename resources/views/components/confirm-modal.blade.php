<!-- Global Confirmation Modal Component -->
<div id="confirmModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="confirmModalTitle" role="dialog" aria-modal="true">
    <!-- Backdrop overlay without blur -->
    <div id="confirmModalBackdrop" class="fixed inset-0 bg-slate-900/50 transition-opacity opacity-0 duration-200"></div>

    <div class="fixed inset-0 z-10 w-screen overflow-y-auto p-4 flex min-h-full items-center justify-center text-center">
        <!-- Modal Card Container -->
        <div id="confirmModalPanel" class="relative transform overflow-hidden rounded-2xl bg-white p-6 text-left shadow-2xl transition-all border border-slate-200 w-full max-w-sm scale-95 opacity-0 duration-200 sm:p-6">
            <div class="flex items-start gap-4">
                <!-- Icon badge with soft background -->
                <div id="confirmModalIconBg" class="mx-auto flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-xl bg-rose-50 text-rose-600 sm:mx-0">
                    <i id="confirmModalIcon" class="fa-solid fa-triangle-exclamation text-lg"></i>
                </div>
                
                <div class="flex-1 min-w-0">
                    <h3 id="confirmModalTitle" class="text-base font-bold text-slate-900 leading-snug">
                        Hapus Faktur Tagihan?
                    </h3>
                    <div class="mt-1.5">
                        <p id="confirmModalMessage" class="text-xs text-slate-500 leading-relaxed">
                            Apakah Anda yakin ingin menghapus faktur ini? Tindakan ini tidak dapat dibatalkan.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="mt-6 flex flex-row justify-end gap-2.5 sm:mt-5">
                <button type="button" id="confirmModalCancelBtn" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition-all cursor-pointer">
                    Batal
                </button>
                <button type="button" id="confirmModalConfirmBtn" class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold shadow-sm transition-all cursor-pointer">
                    Hapus
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    (function() {
        const modal = document.getElementById('confirmModal');
        const backdrop = document.getElementById('confirmModalBackdrop');
        const panel = document.getElementById('confirmModalPanel');
        const titleEl = document.getElementById('confirmModalTitle');
        const messageEl = document.getElementById('confirmModalMessage');
        const confirmBtn = document.getElementById('confirmModalConfirmBtn');
        const cancelBtn = document.getElementById('confirmModalCancelBtn');
        const iconBg = document.getElementById('confirmModalIconBg');
        const icon = document.getElementById('confirmModalIcon');

        let pendingCallback = null;

        function showModal(options = {}) {
            const {
                title = 'Konfirmasi',
                message = 'Apakah Anda yakin ingin melanjutkan?',
                confirmText = 'Lanjutkan',
                cancelText = 'Batal',
                type = 'danger', // 'danger' | 'warning' | 'primary'
                onConfirm = () => {}
            } = options;

            titleEl.textContent = title;
            messageEl.textContent = message;
            confirmBtn.textContent = confirmText;
            cancelBtn.textContent = cancelText;
            pendingCallback = onConfirm;

            // Type styling
            if (type === 'danger') {
                iconBg.className = 'mx-auto flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-xl bg-rose-50 text-rose-600 sm:mx-0';
                icon.className = 'fa-solid fa-triangle-exclamation text-lg';
                confirmBtn.className = 'px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold shadow-sm transition-all cursor-pointer';
            } else if (type === 'warning') {
                iconBg.className = 'mx-auto flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-xl bg-amber-50 text-amber-600 sm:mx-0';
                icon.className = 'fa-solid fa-circle-exclamation text-lg';
                confirmBtn.className = 'px-4 py-2 rounded-xl bg-amber-600 hover:bg-amber-700 text-white text-xs font-semibold shadow-sm transition-all cursor-pointer';
            } else {
                iconBg.className = 'mx-auto flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 sm:mx-0';
                icon.className = 'fa-solid fa-circle-question text-lg';
                confirmBtn.className = 'px-4 py-2 rounded-xl btn-primary text-white text-xs font-semibold shadow-sm transition-all cursor-pointer';
            }

            modal.classList.remove('hidden');
            requestAnimationFrame(() => {
                backdrop.classList.remove('opacity-0');
                backdrop.classList.add('opacity-100');
                panel.classList.remove('scale-95', 'opacity-0');
                panel.classList.add('scale-100', 'opacity-100');
            });
        }

        function closeModal() {
            backdrop.classList.remove('opacity-100');
            backdrop.classList.add('opacity-0');
            panel.classList.remove('scale-100', 'opacity-100');
            panel.classList.add('scale-95', 'opacity-0');
            setTimeout(() => {
                modal.classList.add('hidden');
                pendingCallback = null;
            }, 200);
        }

        cancelBtn.addEventListener('click', closeModal);
        backdrop.addEventListener('click', closeModal);

        confirmBtn.addEventListener('click', function() {
            if (typeof pendingCallback === 'function') {
                if (window.setButtonLoading) {
                    window.setButtonLoading(confirmBtn);
                }
                const cb = pendingCallback;
                pendingCallback = null;
                cb();
            }
            closeModal();
        });

        // ESC key to close
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
                closeModal();
            }
        });

        // Global function window.showConfirmModal
        window.showConfirmModal = showModal;

        // Auto attach listener to forms or buttons with data-confirm
        document.addEventListener('click', function(e) {
            const trigger = e.target.closest('[data-confirm]');
            if (!trigger) return;

            e.preventDefault();
            const message = trigger.getAttribute('data-confirm') || 'Apakah Anda yakin ingin melakukan tindakan ini?';
            const title = trigger.getAttribute('data-confirm-title') || 'Konfirmasi Tindakan';
            const confirmText = trigger.getAttribute('data-confirm-btn') || 'Ya, Lanjutkan';
            const type = trigger.getAttribute('data-confirm-type') || 'danger';

            const form = trigger.closest('form');

            showModal({
                title: title,
                message: message,
                confirmText: confirmText,
                type: type,
                onConfirm: function() {
                    if (form) {
                        form.submit();
                    } else if (trigger.tagName === 'A' && trigger.href) {
                        window.location.href = trigger.href;
                    }
                }
            });
        });
    })();
</script>
