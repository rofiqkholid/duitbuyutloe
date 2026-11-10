@extends('layouts.main')

@section('title', 'Kompensasi & Saling Potong Hutang')

@section('main-content')
<div class="space-y-6">
    <!-- Top Bar -->
    <div class="flex items-center gap-2.5 sm:gap-3">
        <a href="{{ route('dashboard') }}" class="inline-flex items-center justify-center gap-2 px-3 py-2 sm:px-4 sm:py-2 rounded-xl bg-white border border-slate-200 text-xs font-bold text-slate-700 hover:text-slate-900 hover:bg-slate-50 transition-colors flex-shrink-0" title="Kembali ke Dashboard">
            <i class="fa-solid fa-arrow-left-long"></i>
            <span class="hidden sm:inline">Dashboard</span>
        </a>
        <span class="text-xs font-normal text-slate-500">
            Faktur: <a href="{{ route('bills.show', $share->bill->id) }}" class="text-slate-800 font-semibold hover:text-indigo-600 transition-colors">{{ $share->bill->title }}</a>
        </span>
    </div>

    <!-- Main Card -->
    <div class="rounded-2xl bg-white border border-slate-200 p-5 sm:p-8 space-y-6">
        <!-- Header -->
        <div class="flex items-start justify-between flex-wrap gap-4 pb-6 border-b border-slate-100">
            <div>
                <h1 class="text-xl sm:text-3xl font-semibold text-slate-900 tracking-tight">
                    Kompensasi & Saling Potong Hutang
                </h1>
                <p class="text-xs sm:text-sm text-slate-500 font-normal mt-1.5">
                    Kalkulasi hutang timbal balik antara kamu (<strong>{{ $me->name }}</strong>) dan <strong>{{ $otherUser->name }}</strong>.
                </p>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('bills.show', $share->bill->id) }}" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition-all">
                    Lihat Faktur
                </a>
            </div>
        </div>

        <!-- 3 Financial Summary Cards (Konsisten dengan layout kartu di aplikasi) -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-stretch">
            <!-- Box 1: Hutang Lu ke Dia -->
            <div class="p-5 rounded-xl bg-slate-50 border border-slate-200 flex flex-col justify-between">
                <div>
                    <span class="text-xs text-slate-500 font-medium block mb-1">
                        Hutang lu ke {{ $otherUser->name }}
                    </span>
                    <div class="text-2xl sm:text-3xl font-bold text-rose-600">
                        Rp {{ number_format($totalMyDebt, 0, ',', '.') }}
                    </div>
                </div>
                <div class="pt-3 mt-3 border-t border-slate-200/70 text-xs text-slate-500">
                    {{ $countMyDebt }} tagihan tercatat
                </div>
            </div>

            <!-- Box 2: Hutang Dia ke Lu -->
            <div class="p-5 rounded-xl bg-slate-50 border border-slate-200 flex flex-col justify-between">
                <div>
                    <span class="text-xs text-slate-500 font-medium block mb-1">
                        Hutang {{ $otherUser->name }} ke lu
                    </span>
                    <div class="text-2xl sm:text-3xl font-bold text-emerald-600">
                        Rp {{ number_format($totalOtherDebt, 0, ',', '.') }}
                    </div>
                </div>
                <div class="pt-3 mt-3 border-t border-slate-200/70 text-xs text-slate-500">
                    {{ $countOtherDebt }} tagihan tercatat
                </div>
            </div>

            <!-- Box 3: Hasil Potong Hutang (Netting) -->
            <div class="p-5 rounded-xl bg-slate-50 border border-slate-200 flex flex-col justify-between">
                <div>
                    <span class="text-xs text-slate-500 font-medium block mb-1">
                        @if($offsetAmount > 0)
                            @if($netBalance <= 0 || $share->status === 'approved')
                                Status setelah dipotong
                            @else
                                Sisa hutang lu setelah dipotong
                            @endif
                        @else
                            Status tagihan
                        @endif
                    </span>
                    <div class="text-2xl sm:text-3xl font-bold text-slate-900">
                        @if($netBalance <= 0 || $share->status === 'approved')
                            Lunas (Rp 0)
                        @else
                            Rp {{ number_format($share->amount, 0, ',', '.') }}
                        @endif
                    </div>
                </div>
                <div class="pt-3 mt-3 border-t border-slate-200/70 text-xs text-slate-500">
                    @if($offsetAmount > 0)
                        @if($netBalance < 0)
                            Sisa hutang {{ $otherUser->name }}: Rp {{ number_format(abs($netBalance), 0, ',', '.') }}
                        @else
                            Otomatis dipotong: Rp {{ number_format($offsetAmount, 0, ',', '.') }}
                        @endif
                    @else
                        Tidak ada hutang timbal balik
                    @endif
                </div>
            </div>
        </div>

        <!-- Section Instruksi Transfer & Form Bayar (Konsisten dengan bills/show.blade.php) -->
        <div class="pt-6 border-t border-slate-100">
            <div class="pb-4 mb-4">
                <h3 class="text-base font-bold text-slate-900">
                    Instruksi Pembayaran & Transfer
                </h3>
                <p class="text-xs text-slate-500 font-normal mt-1">
                    @if($share->status === 'approved')
                        Tagihan ini sudah disetujui / dipotong lunas. Kamu tidak perlu mentransfer apa pun.
                    @elseif($share->status === 'paid')
                        Bukti pembayaran sudah dikirim dan sedang menunggu pengecekan dari {{ $otherUser->name }}.
                    @else
                        Transfer sisa tagihan ke rekening {{ $otherUser->name }} lalu kirimkan konfirmasi beserta bukti transfernya.
                    @endif
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-stretch">
                <!-- Box Rekening Tujuan -->
                <div class="p-5 rounded-xl bg-slate-50 border border-slate-200 flex flex-col justify-between">
                    <div>
                        <span class="text-xs text-slate-500 font-medium block mb-2">
                            Tujuan transfer
                        </span>
                        <div class="text-base font-medium text-slate-800">{{ $otherUser->name }}</div>
                        @if($paymentMethod)
                            <div class="mt-1.5 space-y-1">
                                <div class="text-xs text-slate-700 font-medium flex items-center gap-1.5 flex-wrap">
                                    <span>{{ $paymentMethod->type }}:</span>
                                    <span class="font-mono text-slate-900 font-semibold select-all">{{ $paymentMethod->account_number }}</span>
                                </div>
                                <div class="flex items-center justify-between gap-2">
                                    <div class="text-xs text-slate-500 font-normal">
                                        a/n {{ $paymentMethod->account_name }}
                                    </div>
                                    <button type="button" 
                                            onclick="copyAccountNumber('{{ $paymentMethod->account_number }}', this)" 
                                            title="Salin No. Rek"
                                            class="px-2.5 py-1 rounded-lg bg-white border border-slate-200 hover:bg-slate-100 text-slate-600 hover:text-slate-900 transition-colors flex items-center gap-1.5 flex-shrink-0 cursor-pointer">
                                        <span class="copy-default flex items-center gap-1.5">
                                            <i data-lucide="copy" class="w-3.5 h-3.5"></i>
                                            <span class="text-xs font-semibold">Salin<span class="hidden sm:inline"> No. Rek</span></span>
                                        </span>
                                        <span class="copy-success hidden items-center gap-1.5 text-emerald-600 font-bold">
                                            <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                            <span class="text-xs">Tersalin!</span>
                                        </span>
                                    </button>
                                </div>

                                @if($paymentMethod->qr_code_image)
                                    <div class="pt-3 mt-3 border-t border-slate-200">
                                        <span class="text-[11px] text-slate-500 font-medium block mb-1.5">QRIS Pembayaran:</span>
                                        <img src="{{ asset('storage/' . $paymentMethod->qr_code_image) }}" alt="QRIS {{ $otherUser->name }}" class="max-w-[140px] rounded-lg border border-slate-200 cursor-pointer hover:opacity-90 transition-opacity" onclick="window.open(this.src, '_blank')">
                                    </div>
                                @endif
                            </div>
                        @else
                            <div class="text-xs text-slate-500 font-normal mt-1">
                                Belum ngisi rekening. Samperin orangnya, bayar tunai ke {{ $otherUser->name }}.
                            </div>
                        @endif
                    </div>

                    <div class="pt-4 mt-4 border-t border-slate-200/70 flex items-center justify-between text-xs">
                        <span class="text-slate-500 font-normal">Porsi yang harus kamu bayar:</span>
                        <span class="text-xs text-slate-700">
                            @if($share->status === 'approved' || $netBalance <= 0)
                                Rp 0 (lunas)
                            @else
                                Rp {{ number_format($share->amount, 0, ',', '.') }}
                            @endif
                        </span>
                    </div>

                    @if($netBalance < 0)
                        <div class="pt-2 mt-2 border-t border-slate-200/70 flex items-center justify-between text-xs text-slate-500">
                            <span>Sisa hutang {{ $otherUser->name }} ke kamu:</span>
                            <span class="text-xs text-slate-700">
                                Rp {{ number_format(abs($netBalance), 0, ',', '.') }}
                            </span>
                        </div>
                    @endif
                </div>

                <!-- Box Form Pembayaran / Status Konfirmasi -->
                <div class="p-5 rounded-xl bg-slate-50 border border-slate-200 flex flex-col justify-between">
                    @if($share->status === 'approved' || $netBalance <= 0)
                        <form action="{{ route('settlements.confirm', $share->id) }}" method="POST" class="space-y-4">
                            @csrf
                            <div class="p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-xs text-emerald-800 leading-relaxed">
                                <div class="font-bold text-sm text-emerald-900 flex items-center gap-2 mb-1">
                                    <i class="fa-solid fa-circle-check text-emerald-600"></i>
                                    <span>Hutang Lu Lunas Terpotong!</span>
                                </div>
                                @if($netBalance < 0)
                                    Hutang kamu ke {{ $otherUser->name }} telah dipotong lunas dari hutang {{ $otherUser->name }}. Sisa hutang <strong>{{ $otherUser->name }}</strong> ke kamu sekarang adalah <strong>Rp {{ number_format(abs($netBalance), 0, ',', '.') }}</strong>.
                                @else
                                    Hutang kamu ke {{ $otherUser->name }} telah dipotong lunas secara timbal balik.
                                @endif
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-slate-800 mb-2">
                                    Catatan Konfirmasi <span class="text-xs font-normal text-slate-400">(Opsional)</span>
                                </label>
                                <input 
                                    type="text" 
                                    name="notes" 
                                    placeholder="Contoh: Sudah saling potong hutang ya bro" 
                                    class="w-full h-11 px-3.5 rounded-xl bg-white border border-slate-300 text-slate-900 text-sm focus:outline-none focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600"
                                >
                            </div>

                            <button type="submit" class="w-full h-11 px-4 rounded-xl font-bold text-sm bg-emerald-600 hover:bg-emerald-700 text-white transition-all shadow-sm flex items-center justify-center gap-2 cursor-pointer">
                                <i class="fa-solid fa-circle-check text-xs"></i>
                                <span>Konfirmasi Lunas @if($netBalance < 0)(Sisa Hutang {{ $otherUser->name }}: Rp {{ number_format(abs($netBalance), 0, ',', '.') }})@endif</span>
                            </button>
                        </form>
                    @elseif($share->status === 'paid')
                        <div class="space-y-3">
                            <span class="text-xs text-slate-500 font-medium block">
                                Status Pembayaran
                            </span>
                            <div class="p-3.5 rounded-xl bg-amber-50 border border-amber-200 text-xs text-amber-800">
                                <strong>Menunggu Verifikasi:</strong> Bukti transfer sudah kamu kirim pada {{ \Carbon\Carbon::parse($share->paid_at)->format('d M Y H:i') }}. Tunggu konfirmasi dari {{ $otherUser->name }}.
                            </div>
                            @if($share->proof_image)
                                <div>
                                    <a href="{{ asset('storage/' . $share->proof_image) }}" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white border border-slate-200 text-slate-700 hover:bg-slate-100 text-xs font-semibold transition-colors">
                                        <i class="fa-solid fa-image"></i>
                                        <span>Lihat Bukti Transfer</span>
                                    </a>
                                </div>
                            @endif
                        </div>
                    @else
                        <form action="{{ route('shares.pay', $share->id) }}" method="POST" enctype="multipart/form-data" class="space-y-3">
                            @csrf
                            <span class="text-xs text-slate-500 font-medium block">
                                Konfirmasi Pembayaran
                            </span>

                            <div>
                                <label class="block text-sm font-bold text-slate-800 mb-2">
                                    Foto Bukti / Struk Transfer <span class="text-xs font-normal text-slate-400">(Opsional)</span>
                                </label>
                                <input 
                                    type="file" 
                                    id="proof_image" 
                                    name="proof_image" 
                                    accept="image/*" 
                                    class="file-btn-primary w-full px-3.5 py-2.5 rounded-xl bg-white border border-dashed border-slate-300 text-slate-500 text-xs cursor-pointer"
                                >
                                @error('proof_image')
                                    <span class="text-xs text-rose-600 font-medium mt-1 block">{{ $message }}</span>
                                @enderror

                                <!-- Progress Bar 0 - 100% -->
                                <div id="uploadProgressBarContainer" class="hidden mt-2.5 p-3 rounded-xl bg-white border border-slate-200 space-y-1.5 transition-all">
                                    <div class="flex items-center justify-between text-xs">
                                        <span id="uploadProgressText" class="font-medium text-slate-600 flex items-center gap-1.5">
                                            <i class="fa-solid fa-spinner fa-spin text-slate-400"></i> Memuat bukti...
                                        </span>
                                        <span id="uploadProgressPercent" class="font-medium text-slate-600">0%</span>
                                    </div>
                                    <div class="w-full bg-slate-200 rounded-full h-2 overflow-hidden">
                                        <div id="uploadProgressBar" class="h-full rounded-full transition-all duration-150 ease-out bg-rose-500" style="width: 0%;"></div>
                                    </div>
                                </div>

                                <!-- Info Bukti Terupload -->
                                <div id="fileInfoCard" class="hidden mt-2.5 p-3 rounded-xl bg-white border border-slate-200 flex items-center justify-between gap-3 transition-all">
                                    <div class="flex items-center gap-2.5 min-w-0">
                                        <div id="fileThumbnailContainer" class="w-10 h-10 rounded-lg bg-slate-50 border border-slate-200 flex items-center justify-center flex-shrink-0 overflow-hidden text-slate-500 shadow-2xs">
                                            <i class="fa-solid fa-file-invoice text-base text-indigo-600"></i>
                                        </div>
                                        <div class="min-w-0">
                                            <span id="fileName" class="text-xs font-bold text-slate-800 truncate block"></span>
                                            <span id="fileSize" class="text-[10px] text-slate-500 mt-0.5 block"></span>
                                        </div>
                                    </div>
                                    <button 
                                        type="button" 
                                        id="removeFileBtn" 
                                        class="px-2 py-1 rounded-lg text-xs font-medium text-slate-500 hover:text-rose-600 hover:bg-rose-50 transition-colors flex-shrink-0 cursor-pointer"
                                        title="Hapus file terpilih"
                                    >
                                        Hapus
                                    </button>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-slate-800 mb-2">
                                    Catatan Transfer <span class="text-xs font-normal text-slate-400">(Opsional)</span>
                                </label>
                                <input 
                                    type="text" 
                                    name="notes" 
                                    placeholder="Contoh: Sudah transfer ya bro" 
                                    class="w-full h-11 px-3.5 rounded-xl bg-white border border-slate-300 text-slate-900 text-sm focus:outline-none focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600"
                                >
                            </div>

                            <div class="pt-2">
                                <button type="submit" class="w-full h-11 px-4 rounded-xl font-bold text-sm bg-emerald-600 hover:bg-emerald-700 text-white transition-all shadow-sm flex items-center justify-center gap-2 cursor-pointer">
                                    <i class="fa-solid fa-paper-plane text-xs"></i>
                                    <span>Kirim Konfirmasi Bayar (Rp {{ number_format($share->amount, 0, ',', '.') }})</span>
                                </button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>

    </div>
</div>

@push('scripts')
<script>
    function copyAccountNumber(text, btn) {
        if (!navigator.clipboard) {
            const textarea = document.createElement('textarea');
            textarea.value = text;
            document.body.appendChild(textarea);
            textarea.select();
            document.execCommand('copy');
            document.body.removeChild(textarea);
            showCopiedFeedback(btn);
            return;
        }

        navigator.clipboard.writeText(text).then(() => {
            showCopiedFeedback(btn);
        }).catch(err => {
            console.error('Gagal menyalin:', err);
        });
    }

    function showCopiedFeedback(btn) {
        const defState = btn.querySelector('.copy-default');
        const successState = btn.querySelector('.copy-success');
        
        if (defState && successState) {
            defState.classList.add('hidden');
            defState.classList.remove('flex');
            successState.classList.remove('hidden');
            successState.classList.add('flex');
            btn.classList.add('border-emerald-300', 'bg-emerald-50');

            setTimeout(() => {
                successState.classList.add('hidden');
                successState.classList.remove('flex');
                defState.classList.remove('hidden');
                defState.classList.add('flex');
                btn.classList.remove('border-emerald-300', 'bg-emerald-50');
            }, 2000);
        }
    }

    // Handler Upload Bukti Transfer & Progress Bar (Sama persis seperti di Upload Faktur)
    const fileInput = document.getElementById('proof_image');
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
                    progressBar.classList.add('bg-rose-500');
                } else if (pct < 80) {
                    progressBar.classList.add('bg-amber-500');
                } else {
                    progressBar.classList.add('bg-emerald-500');
                }
            }

            function startProcessing(finalFile) {
                infoCard.classList.add('hidden');
                progressContainer.classList.remove('hidden');
                progressBar.style.width = '0%';
                updateProgressColor(0);
                progressPercent.textContent = '0%';
                progressText.innerHTML = '<i class="fa-solid fa-spinner fa-spin text-slate-400"></i> Memuat bukti...';

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
                                fileThumbContainer.innerHTML = '<i class="fa-solid fa-file-invoice text-xl text-indigo-600"></i>';
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

            // Auto-compress foto jika di atas 1.5MB
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
@endpush
@endsection
