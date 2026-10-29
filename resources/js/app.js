import './bootstrap';
import { createIcons, icons } from 'lucide';
import Viewer from 'viewerjs';

window.createIcons = createIcons;
window.lucideIcons = icons;
window.Viewer = Viewer;

// Spinner HTML generator
function getSpinnerHTML(hasText = true) {
    const marginClass = hasText ? '-ml-0.5 mr-2' : '';
    return `<svg class="animate-spin ${marginClass} h-4 w-4 inline-block flex-shrink-0" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
    </svg>`;
}

// Function to apply loading state to a button / link
window.setButtonLoading = function(button, loadingText = null) {
    if (!button || button.dataset.isLoading === 'true') return;

    // Kunci dimensi eksak (width & height) agar ukuran tombol tidak berubah sama sekali
    const rect = button.getBoundingClientRect();
    if (rect.width > 0) {
        button.style.width = `${rect.width}px`;
    }
    if (rect.height > 0) {
        button.style.height = `${rect.height}px`;
    }

    button.dataset.isLoading = 'true';
    button.dataset.originalHtml = button.innerHTML;
    button.classList.add('opacity-75', 'cursor-not-allowed', 'pointer-events-none', 'inline-flex', 'items-center', 'justify-center');

    const textToDisplay = loadingText !== null ? loadingText : (button.getAttribute('data-loading-text') || 'Memproses...');

    if (textToDisplay && textToDisplay.trim() !== '') {
        button.innerHTML = `<span class="inline-flex items-center justify-center">${getSpinnerHTML(true)}<span>${textToDisplay}</span></span>`;
    } else {
        // Hanya spinner di tengah tanpa teks
        button.innerHTML = `<span class="inline-flex items-center justify-center w-full h-full">${getSpinnerHTML(false)}</span>`;
    }
};

// Reset loading state if needed
window.resetButtonLoading = function(button) {
    if (!button || button.dataset.isLoading !== 'true') return;
    if (button.dataset.originalHtml) {
        button.innerHTML = button.dataset.originalHtml;
    }
    button.removeAttribute('data-is-loading');
    button.removeAttribute('data-original-html');
    button.style.width = '';
    button.style.height = '';
    button.classList.remove('opacity-75', 'cursor-not-allowed', 'pointer-events-none');
    createIcons({ icons });
};

document.addEventListener('DOMContentLoaded', () => {
    createIcons({ icons });

    // Handle button loading saat form disubmit
    document.addEventListener('submit', (e) => {
        const form = e.target;
        if (!form) return;

        // Cari tombol submit dalam form atau tombol luar yang terkait via attribute form=""
        let submitBtn = form.querySelector('button[type="submit"]:not([data-no-loading])');
        if (!submitBtn && form.id) {
            submitBtn = document.querySelector(`button[type="submit"][form="${form.id}"]:not([data-no-loading])`);
        }

        if (submitBtn) {
            const btnText = submitBtn.textContent.trim().toLowerCase();
            const isCompactSubmit = btnText.includes('lunas') || 
                                    btnText.includes('hapus') || 
                                    btnText === 'ya' || 
                                    btnText === 'ok';
            window.setButtonLoading(submitBtn, isCompactSubmit ? '' : null);
        }
    });

    // Handle klik pada link yang berfungsi seperti tombol (misal: Rincian tabel, tombol navigasi)
    document.addEventListener('click', (e) => {
        // Tombol manual data-loading
        const btn = e.target.closest('button[data-loading]');
        if (btn && btn.type !== 'submit') {
            window.setButtonLoading(btn);
            return;
        }

        // Tautan navigasi (misal tombol Rincian, btn-primary berwujud <a>, atau link dengan href nyata)
        const link = e.target.closest('a');
        if (link && link.href) {
            const isModifiedClick = e.metaKey || e.ctrlKey || e.shiftKey || e.altKey;
            const isExternalOrBlank = link.target === '_blank' || link.getAttribute('target') === '_blank';
            const isAnchorOnly = link.getAttribute('href').startsWith('#') || link.getAttribute('href').startsWith('javascript:');

            if (!isModifiedClick && !isExternalOrBlank && !isAnchorOnly) {
                // Periksa apakah link tersebut bergaya tombol (seperti tombol Rincian di tabel atau tombol aksi)
                const isButtonLike = link.classList.contains('btn-primary') ||
                                     link.classList.contains('rounded-lg') ||
                                     link.classList.contains('rounded-xl') ||
                                     link.classList.contains('rounded-full') ||
                                     link.matches('td a');

                if (isButtonLike && !link.hasAttribute('data-no-loading')) {
                    const textLower = link.textContent.trim().toLowerCase();
                    // Tombol aksi ringkas (Rincian, Edit bon, Bukti, Batal, dll) hanya menampilkan spinner tanpa teks
                    const isSpinnerOnly = link.matches('td a') || 
                                          textLower === 'rincian' || 
                                          textLower.startsWith('edit') || 
                                          textLower === 'bukti' || 
                                          textLower === 'batal' ||
                                          textLower === 'gajadi deh';

                    window.setButtonLoading(link, isSpinnerOnly ? '' : (link.textContent.trim() || 'Memuat...'));
                }
            }
        }
    });
});

// Function to reset all loading buttons across the page
window.resetAllLoadingButtons = function() {
    document.querySelectorAll('[data-is-loading="true"]').forEach((el) => {
        window.resetButtonLoading(el);
    });
};

// Reset loading saat user kembali dari halaman lain via browser back button (bfcache)
window.addEventListener('pageshow', (event) => {
    window.resetAllLoadingButtons();
});

window.addEventListener('popstate', () => {
    window.resetAllLoadingButtons();
});

