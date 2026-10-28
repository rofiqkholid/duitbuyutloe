@extends('layouts.app')

@section('title', 'Edit Faktur Tagihan')

@section('content')
<div class="space-y-6">
    <!-- Top Bar -->
    <div class="flex items-center gap-2.5 sm:gap-3">
        <a href="{{ route('bills.show', $bill->id) }}" class="inline-flex items-center justify-center gap-2 px-3 py-2 sm:px-4 sm:py-2 rounded-xl bg-white border border-slate-200 text-xs font-bold text-slate-700 hover:text-slate-900 hover:bg-slate-50 transition-colors flex-shrink-0" title="Kembali ke Detail Faktur">
            <i class="fa-solid fa-arrow-left-long"></i>
            <span class="hidden sm:inline">Detail Faktur</span>
        </a>
        <span class="text-xs font-normal text-slate-500">
            Tipe: <span class="text-slate-800 font-semibold">{{ $bill->type ?: $bill->category }}</span>
        </span>
    </div>

    <!-- Main Card -->
    <div class="rounded-2xl bg-white border border-slate-200 p-5 sm:p-8 space-y-6">
        <div class="pb-6 border-b border-slate-100">
            <h1 class="text-xl sm:text-3xl font-semibold text-slate-900 tracking-tight">
                Edit Faktur Tagihan
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 font-normal mt-1.5">
                Perbarui rincian nominal atau foto faktur tagihan.
            </p>
        </div>

        <form action="{{ route('bills.update', $bill->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Siapa yang menalangi (Otomatis akun pembuat) -->
            <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-between flex-wrap gap-3">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-slate-200 text-slate-700 font-bold text-sm flex items-center justify-center flex-shrink-0">
                        {{ substr($bill->payer->name, 0, 1) }}
                    </div>
                    <div>
                        <span class="text-xs text-slate-500 font-medium block">Ditalangi oleh (Pembuat Faktur):</span>
                        <div class="text-sm font-bold text-slate-900">{{ $bill->payer->name }}</div>
                    </div>
                </div>
                <div class="text-left sm:text-right">
                    <span class="text-xs text-slate-500 font-medium block">Rekening tujuan transfer:</span>
                    <span class="text-xs text-slate-700">
                        {{ $bill->payer->paymentMethods->first()?->type ?? 'Belum ada rekening' }}
                        @if($bill->payer->paymentMethods->first())
                            &bull; {{ $bill->payer->paymentMethods->first()->account_number }}
                        @endif
                    </span>
                </div>
            </div>

            <!-- Kategori & Judul -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-bold text-slate-800 mb-2">
                        Type (Tipe Tagihan) <span class="text-rose-600">*</span>
                    </label>
                    <x-custom-select 
                        name="type" 
                        :selected="old('type', strtolower($bill->type))" 
                        :options="[
                            'wifi' => '🌐 Wifi / Internet',
                            'listrik' => '⚡ Listrik / PLN',
                            'air' => '💧 Air PDAM',
                            'galon' => '🥤 Galon / Gas',
                            'kebersihan' => '🧹 Kebersihan',
                            'lainnya' => '📦 Lainnya'
                        ]" 
                        required 
                    />
                </div>
                <div>
                    <label class="block text-sm font-bold text-slate-800 mb-2">
                        Judul Faktur <span class="text-xs font-normal text-slate-400">(Opsional)</span>
                    </label>
                    <input type="text" name="title" value="{{ old('title', $bill->title) }}" placeholder="Kosongkan untuk otomatis 'Tagihan [Jenis]'" class="w-full h-11 px-3.5 rounded-xl bg-white border border-slate-300 text-slate-900 text-sm focus:outline-none focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600">
                </div>
            </div>

            <!-- Total Nominal & Tanggal -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-bold text-slate-800 mb-2">
                        Total Faktur (Rp) <span class="text-rose-600">*</span>
                    </label>
                    <input type="hidden" id="total_amount" name="total_amount" value="{{ old('total_amount', (int)$bill->total_amount) }}" required>
                    <input type="text" id="total_amount_display" inputmode="numeric" placeholder="100.000" required class="w-full h-11 px-3.5 rounded-xl bg-white border border-slate-300 text-slate-900 text-sm focus:outline-none focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600">
                    <div id="splitPreview" class="mt-2 text-xs text-slate-500">
                        Bagi 5 orang: Rp 0 / orang
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-bold text-slate-800 mb-2">
                        Tanggal Faktur <span class="text-rose-600">*</span>
                    </label>
                    <input type="date" name="bill_date" value="{{ old('bill_date', \Carbon\Carbon::parse($bill->bill_date)->format('Y-m-d')) }}" required class="w-full h-11 px-3.5 rounded-xl bg-white border border-slate-300 text-slate-800 text-sm focus:outline-none focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600">
                </div>
            </div>

            <!-- Foto Faktur -->
            <div>
                <label class="block text-sm font-bold text-slate-800 mb-2">
                    Foto / Kuitansi Faktur Tagihan (Opsional)
                </label>
                @if($bill->invoice_image)
                    <div class="mb-3 p-3.5 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3 min-w-0">
                            @if(\Illuminate\Support\Str::endsWith(strtolower($bill->invoice_image), ['.pdf']))
                                <div class="w-12 h-12 rounded-lg bg-white border border-slate-200 flex items-center justify-center flex-shrink-0 text-rose-500 shadow-2xs">
                                    <i class="fa-solid fa-file-pdf text-xl"></i>
                                </div>
                            @else
                                <div class="w-12 h-12 rounded-lg bg-white border border-slate-200 overflow-hidden flex-shrink-0 shadow-2xs">
                                    <img src="{{ asset('storage/' . $bill->invoice_image) }}" alt="Foto Faktur" class="w-full h-full object-cover">
                                </div>
                            @endif
                            <div class="min-w-0">
                                <span class="text-xs font-bold text-slate-800 block truncate">Foto Faktur Tersimpan</span>
                                <span class="text-[11px] text-slate-500 block truncate">{{ basename($bill->invoice_image) }}</span>
                            </div>
                        </div>
                        <a href="{{ asset('storage/' . $bill->invoice_image) }}" target="_blank" class="px-3 py-1.5 rounded-lg bg-white border border-slate-200 text-xs font-semibold text-slate-700 hover:bg-slate-100 hover:text-slate-900 transition-colors flex-shrink-0">
                            Lihat Foto
                        </a>
                    </div>
                @else
                    <div class="mb-3 p-3 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-500 font-normal flex items-center gap-2">
                        <i class="fa-solid fa-circle-info text-slate-400"></i>
                        <span>Belum ada foto/kuitansi faktur yang tersimpan untuk tagihan ini. Silakan pilih file di bawah jika ingin mengunggah.</span>
                    </div>
                @endif
                <input 
                    type="file" 
                    id="invoice_image" 
                    name="invoice_image" 
                    accept="image/*,application/pdf" 
                    class="file-btn-primary w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-dashed border-slate-300 text-slate-500 text-xs cursor-pointer"
                >
                <span class="text-[11px] text-slate-400 mt-1 block">Biarkan kosong jika tidak ingin mengubah foto faktur. Maks 5MB.</span>
                @error('invoice_image')
                    <span class="text-xs text-rose-600 font-medium mt-1 block">{{ $message }}</span>
                @enderror

                <!-- Progress Bar 0 - 100% -->
                <div id="uploadProgressBarContainer" class="hidden mt-3 p-3 rounded-xl bg-slate-50 border border-slate-200 space-y-1.5 transition-all">
                    <div class="flex items-center justify-between text-xs">
                        <span id="uploadProgressText" class="font-medium text-slate-600 flex items-center gap-1.5">
                            <i class="fa-solid fa-spinner fa-spin text-slate-400"></i> Memuat faktur...
                        </span>
                        <span id="uploadProgressPercent" class="font-medium text-slate-600">0%</span>
                    </div>
                    <div class="w-full bg-slate-200 rounded-full h-2 overflow-hidden">
                        <div id="uploadProgressBar" class="h-full rounded-full transition-all duration-150 ease-out bg-rose-500" style="width: 0%;"></div>
                    </div>
                </div>

                <!-- Info Faktur Baru Terupload -->
                <div id="fileInfoCard" class="hidden mt-3 p-3.5 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-between gap-3 transition-all">
                    <div class="flex items-center gap-3 min-w-0">
                        <div id="fileThumbnailContainer" class="w-11 h-11 rounded-lg bg-white border border-slate-200 flex items-center justify-center flex-shrink-0 overflow-hidden text-slate-500 shadow-2xs">
                            <i class="fa-solid fa-file-invoice text-lg text-indigo-600"></i>
                        </div>
                        <div class="min-w-0">
                            <span id="fileName" class="text-xs font-bold text-slate-800 truncate block"></span>
                            <span id="fileSize" class="text-[11px] text-slate-500 mt-0.5 block"></span>
                        </div>
                    </div>
                    <button 
                        type="button" 
                        id="removeFileBtn" 
                        class="px-2.5 py-1.5 rounded-lg text-xs font-medium text-slate-500 hover:text-rose-600 hover:bg-rose-50 transition-colors flex-shrink-0 cursor-pointer"
                        title="Hapus file terpilih"
                    >
                        Hapus
                    </button>
                </div>
            </div>

            <!-- Catatan -->
            <div>
                <label class="block text-sm font-bold text-slate-800 mb-2">
                    Catatan Tambahan (Opsional)
                </label>
                <textarea name="description" rows="3" placeholder="Contoh: Sudah dibayar via m-banking. Mohon transfer ke rekening saya ya teman-teman." class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-slate-300 text-slate-900 text-sm focus:outline-none focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600">{{ old('description', $bill->description) }}</textarea>
            </div>

            <!-- Submit -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200">
                <a href="{{ route('bills.show', $bill->id) }}" class="px-5 py-2.5 rounded-xl bg-slate-100 text-slate-700 hover:bg-slate-200 text-sm font-bold transition-colors">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-full font-bold text-sm btn-primary cursor-pointer">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    const displayInput = document.getElementById('total_amount_display');
    const hiddenInput = document.getElementById('total_amount');
    const previewEl = document.getElementById('splitPreview');

    function formatRupiah(val) {
        if (!val) return '';
        return val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }

    function updateCalculations() {
        const rawDigits = displayInput.value.replace(/\D/g, '');
        const numericVal = parseInt(rawDigits, 10) || 0;

        hiddenInput.value = numericVal > 0 ? numericVal : '';
        displayInput.value = numericVal > 0 ? formatRupiah(numericVal) : '';

        const perPerson = Math.round(numericVal / 5);
        previewEl.innerText = `Bagi 5 orang: Rp ${formatRupiah(perPerson)} / orang`;
    }

    displayInput.addEventListener('input', updateCalculations);

    if (hiddenInput.value) {
        displayInput.value = hiddenInput.value;
        updateCalculations();
    }

    // Handler Upload Faktur & Progress Bar 0 - 100%
    const fileInput = document.getElementById('invoice_image');
    const progressContainer = document.getElementById('uploadProgressBarContainer');
    const progressBar = document.getElementById('uploadProgressBar');
    const progressPercent = document.getElementById('uploadProgressPercent');
    const progressText = document.getElementById('uploadProgressText');
    const infoCard = document.getElementById('fileInfoCard');
    const fileName = document.getElementById('fileName');
    const fileSize = document.getElementById('fileSize');
    const fileThumbContainer = document.getElementById('fileThumbnailContainer');
    const removeBtn = document.getElementById('removeFileBtn');

    if (fileInput) {
        fileInput.addEventListener('change', function () {
            const file = this.files[0];
            if (!file) {
                infoCard.classList.add('hidden');
                progressContainer.classList.add('hidden');
                return;
            }

            function updateProgressColor(pct) {
                progressBar.classList.remove('bg-rose-500', 'bg-amber-500', 'bg-emerald-500');
                if (pct < 40) {
                    progressBar.classList.add('bg-rose-500'); // Merah (< 40%)
                } else if (pct < 80) {
                    progressBar.classList.add('bg-amber-500'); // Kuning (40% - 79%)
                } else {
                    progressBar.classList.add('bg-emerald-500'); // Hijau (80% - 100%)
                }
            }

            // Validasi file PDF maksimal 2MB
            if (file.type === 'application/pdf' && file.size > 2 * 1024 * 1024) {
                alert('Ukuran file PDF melebihi 2MB. Silakan pilih file yang lebih kecil.');
                fileInput.value = '';
                infoCard.classList.add('hidden');
                progressContainer.classList.add('hidden');
                return;
            }

            function startProcessing(finalFile) {
                infoCard.classList.add('hidden');
                progressContainer.classList.remove('hidden');
                progressBar.style.width = '0%';
                updateProgressColor(0);
                progressPercent.textContent = '0%';
                progressText.innerHTML = '<i class="fa-solid fa-spinner fa-spin text-slate-400"></i> Memuat faktur...';

                let progress = 0;
                const interval = setInterval(() => {
                    progress += Math.floor(Math.random() * 20) + 12;
                    if (progress >= 100) {
                        progress = 100;
                        clearInterval(interval);

                        progressBar.style.width = '100%';
                        updateProgressColor(100);
                        progressPercent.textContent = '100%';
                        progressText.innerHTML = '<i class="fa-solid fa-check text-emerald-600"></i> Selesai!';

                        setTimeout(() => {
                            progressContainer.classList.add('hidden');

                            let sizeStr = '';
                            if (finalFile.size < 1024) sizeStr = finalFile.size + ' B';
                            else if (finalFile.size < 1024 * 1024) sizeStr = (finalFile.size / 1024).toFixed(1) + ' KB';
                            else sizeStr = (finalFile.size / (1024 * 1024)).toFixed(2) + ' MB';

                            fileName.textContent = finalFile.name;
                            fileSize.textContent = sizeStr;

                            if (finalFile.type.startsWith('image/')) {
                                const reader = new FileReader();
                                reader.onload = function (e) {
                                    fileThumbContainer.innerHTML = `<img src="${e.target.result}" class="w-full h-full object-cover">`;
                                };
                                reader.readAsDataURL(finalFile);
                            } else {
                                fileThumbContainer.innerHTML = '<i class="fa-solid fa-file-pdf text-xl text-rose-500"></i>';
                            }

                            infoCard.classList.remove('hidden');
                        }, 300);
                    } else {
                        progressBar.style.width = progress + '%';
                        updateProgressColor(progress);
                        progressPercent.textContent = progress + '%';
                    }
                }, 60);
            }

            // Auto-compress foto jika di atas 1.5MB agar selalu di bawah batas server 2MB
            if (file.type.startsWith('image/') && file.size > 1.5 * 1024 * 1024) {
                const reader = new FileReader();
                reader.readAsDataURL(file);
                reader.onload = function(event) {
                    const img = new Image();
                    img.src = event.target.result;
                    img.onload = function() {
                        const canvas = document.createElement('canvas');
                        let width = img.width;
                        let height = img.height;
                        const maxDim = 1920;
                        if (width > maxDim || height > maxDim) {
                            if (width > height) {
                                height = Math.round((height * maxDim) / width);
                                width = maxDim;
                            } else {
                                width = Math.round((width * maxDim) / height);
                                height = maxDim;
                            }
                        }
                        canvas.width = width;
                        canvas.height = height;
                        const ctx = canvas.getContext('2d');
                        ctx.drawImage(img, 0, 0, width, height);
                        canvas.toBlob(function(blob) {
                            if (blob) {
                                const compressedFile = new File([blob], file.name.replace(/\.[^.]+$/, '.jpg'), {
                                    type: 'image/jpeg',
                                    lastModified: Date.now()
                                });
                                try {
                                    const dt = new DataTransfer();
                                    dt.items.add(compressedFile);
                                    fileInput.files = dt.files;
                                    startProcessing(compressedFile);
                                } catch (e) {
                                    startProcessing(file);
                                }
                            } else {
                                startProcessing(file);
                            }
                        }, 'image/jpeg', 0.82);
                    };
                };
            } else {
                startProcessing(file);
            }
        });

        if (removeBtn) {
            removeBtn.addEventListener('click', function () {
                fileInput.value = '';
                infoCard.classList.add('hidden');
                progressContainer.classList.add('hidden');
            });
        }
    }
</script>
@endsection
