@extends('layouts.app')

@section('title', $bill->title)

@section('content')
<div class="space-y-6">
    <!-- Top Bar -->
    <div class="flex items-center gap-2.5 sm:gap-3">
        <a href="{{ route('dashboard') }}" class="inline-flex items-center justify-center gap-2 px-3 py-2 sm:px-4 sm:py-2 rounded-xl bg-white border border-slate-200 text-xs font-bold text-slate-700 hover:text-slate-900 hover:bg-slate-50 transition-colors flex-shrink-0" title="Kembali ke Dashboard">
            <i class="fa-solid fa-arrow-left-long"></i>
            <span class="hidden sm:inline">Dashboard</span>
        </a>
        <span class="text-xs font-normal text-slate-500">
            Tipe: <span class="text-slate-800 font-semibold">{{ $bill->type ?: $bill->category }}</span>
        </span>
    </div>

    <!-- Main Bill Card -->
    <div class="rounded-2xl bg-white border border-slate-200 p-5 sm:p-8 space-y-6">
        <div class="flex items-start justify-between flex-wrap gap-4 pb-6 border-b border-slate-100">
            <div>
                <h1 class="text-xl sm:text-3xl font-semibold text-slate-900 tracking-tight">
                    {{ $bill->title }}
                </h1>
                <p class="text-xs sm:text-sm text-slate-500 font-normal mt-1.5">
                    {{ $bill->description ?: 'Gaada catatan apa-apa, pokoknya langsung transfer aja ya.' }}
                </p>
            </div>

            <div class="flex items-center gap-2">
                @php
                    $myShare = Auth::check() ? $bill->shares->where('user_id', Auth::id())->first() : null;
                    $canPay = $myShare && $myShare->status == 'unpaid' && (Auth::id() != $bill->payer_id);
                @endphp

                @if($canPay)
                    <button type="button" onclick="document.getElementById('modal-{{ $myShare->id }}').classList.remove('hidden')" class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-xs sm:text-sm font-bold btn-primary cursor-pointer shadow-none transition-all hover:scale-105" title="Bayar tagihan ini">
                        <i class="fa-solid fa-credit-card text-white text-xs"></i>
                        <span>Bayar Utang</span>
                    </button>
                @endif

                @if(Auth::check() && Auth::id() == $bill->payer_id)
                    <a href="{{ route('bills.edit', $bill->id) }}" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition-all">
                        Edit bon
                    </a>
                    <form action="{{ route('bills.destroy', $bill->id) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="button" 
                                data-confirm="Yakin mau hapus bon tagihan ini? Duit talangan lu bisa lenyap tak berbekas!" 
                                data-confirm-title="Hapus Bon Tagihan" 
                                data-confirm-btn="Ya, Hapus Aja"
                                data-confirm-type="danger"
                                class="px-4 py-2 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 hover:bg-rose-100 text-xs font-semibold transition-all cursor-pointer">
                            Hapus bon
                        </button>
                    </form>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-stretch">
            <!-- Box Rekening Tujuan -->
            <div class="p-5 rounded-xl bg-slate-50 border border-slate-200 flex flex-col justify-between">
                <div>
                    <span class="text-xs text-slate-500 font-medium block mb-2">
                        Tujuan transfer (korban yang nalangin)
                    </span>
                    <div class="text-base font-medium text-slate-800">{{ $bill->payer->name }}</div>
                    @php $pm = $bill->payer->paymentMethods->first(); @endphp
                    @if($pm)
                        <div class="text-xs text-slate-600 font-normal mt-1">
                            {{ $pm->type }}: {{ $pm->account_number }}
                        </div>
                        <div class="text-xs text-slate-500 font-normal">
                            a/n {{ $pm->account_name }}
                        </div>
                    @else
                        <div class="text-xs text-slate-500 font-normal mt-1">
                            Belum ngisi rekening. Samperin orangnya, bayar tunai ke {{ $bill->payer->name }}.
                        </div>
                    @endif
                </div>

                <div class="pt-4 mt-4 border-t border-slate-200/70 flex items-center justify-between text-xs gap-2">
                    <div class="flex items-center gap-1.5 sm:gap-2">
                        <span class="text-slate-500 font-normal">Tanggal terbit dosa:</span>
                        <span class="font-medium text-slate-800">
                            <span class="sm:hidden">{{ \Carbon\Carbon::parse($bill->bill_date)->format('d M Y') }}</span>
                            <span class="hidden sm:inline">{{ \Carbon\Carbon::parse($bill->bill_date)->format('d F Y') }}</span>
                        </span>
                    </div>

                    @if($bill->invoice_image)
                        <img id="invoiceViewerImage" src="{{ asset('storage/' . $bill->invoice_image) }}" class="hidden" alt="Foto Faktur: {{ $bill->title }}">
                        <button type="button" onclick="viewInvoice()" class="inline-flex items-center gap-2 px-3.5 py-1.5 sm:px-4 sm:py-2 rounded-full text-xs sm:text-sm font-bold btn-primary cursor-pointer shadow-none transition-all hover:scale-105 flex-shrink-0" title="Lihat Bukti Bon">
                            <i class="fa-solid fa-image text-white text-xs sm:text-sm"></i>
                            <span>Bukti Bon</span>
                        </button>
                    @endif
                </div>
            </div>

            <!-- Summary Box -->
            <div class="p-5 rounded-xl bg-slate-50 border border-slate-200 flex flex-col justify-between text-center sm:text-left">
                <div>
                    <span class="text-xs text-slate-500 font-medium block mb-1">Total duit yang ditalangin orang ganteng</span>
                    <div class="text-2xl sm:text-3xl font-bold text-slate-900">
                        Rp {{ number_format($bill->total_amount, 0, ',', '.') }}
                    </div>
                </div>

                <div class="pt-4 mt-4 border-t border-slate-200/70 flex items-center justify-between">
                    <div>
                        <span class="text-xs text-slate-500 font-normal block">Porsi patungan per kepala:</span>
                        <span class="text-lg font-normal text-slate-800">
                            Rp {{ number_format($bill->share_amount, 0, ',', '.') }}
                        </span>
                    </div>
                    <span class="text-xs text-slate-400 font-normal">bagi 5 manusia</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Status Pembagian 5 Orang -->
    <div class="rounded-2xl bg-white border border-slate-200 p-4 sm:p-8">
        <h3 class="text-base sm:text-lg font-bold text-slate-900 mb-4 sm:mb-5 flex items-center gap-2">
            <i data-lucide="users" class="w-5 h-5 text-indigo-600"></i>
            <span>Daftar manusia yang wajib patungan</span>
        </h3>

        <div class="overflow-x-auto -mx-4 sm:mx-0 px-4 sm:px-0">
            <table class="w-full text-left text-xs sm:text-sm whitespace-nowrap sm:whitespace-normal">
                <thead class="text-xs text-slate-500 bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="py-2.5 sm:py-3 px-3 sm:px-4 font-bold">Warga</th>
                        <th class="py-2.5 sm:py-3 px-3 sm:px-4 font-bold">Nominal</th>
                        <th class="py-2.5 sm:py-3 px-3 sm:px-4 font-bold">Status</th>
                        <th class="py-2.5 sm:py-3 px-3 sm:px-4 font-bold">Waktu bayar</th>
                        <th class="py-2.5 sm:py-3 px-3 sm:px-4 font-bold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($bill->shares as $share)
                        @php 
                            $isMyShare = Auth::check() && (Auth::id() == $share->user_id);
                            $isViewerPayer = Auth::check() && (Auth::id() == $bill->payer_id);
                        @endphp
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="py-3 px-3 sm:px-4">
                                <div>
                                    <div class="text-xs sm:text-sm font-normal text-slate-800">{{ $share->user->name }}</div>
                                    @if($share->user_id == $bill->payer_id)
                                        <span class="text-[10px] sm:text-[11px] text-slate-500 font-normal block">Orang ganteng nalangin (Auto Bebas)</span>
                                    @elseif($isMyShare)
                                        <span class="text-[10px] sm:text-[11px] text-slate-500 font-normal block">(Akun lu)</span>
                                    @endif
                                </div>
                            </td>
                            <td class="py-3 px-3 sm:px-4 font-semibold text-slate-900 text-xs sm:text-sm">
                                Rp {{ number_format($share->amount, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-3 sm:px-4">
                                @if($share->status == 'approved')
                                    <span class="text-xs font-semibold text-slate-900">
                                        Lunas (Bebas Dosa)
                                    </span>
                                @elseif($share->status == 'paid')
                                    <span class="text-xs font-semibold text-slate-700">
                                        Nunggu dicek {{ $bill->payer->name }}
                                    </span>
                                @else
                                    <span class="text-xs font-medium text-slate-500">
                                        Belum bayar (Buruan!)
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-3 sm:px-4 text-xs text-slate-500">
                                @if($share->paid_at)
                                    <div class="sm:hidden leading-tight">
                                        <div>{{ \Carbon\Carbon::parse($share->paid_at)->format('d M Y') }}</div>
                                        <div class="text-[10px] text-slate-400">{{ \Carbon\Carbon::parse($share->paid_at)->format('H:i') }}</div>
                                    </div>
                                    <span class="hidden sm:inline">{{ \Carbon\Carbon::parse($share->paid_at)->format('d M Y H:i') }}</span>
                                @else
                                    <span>-</span>
                                @endif
                            </td>
                            <td class="py-3 px-3 sm:px-4 text-right">
                                <div class="flex items-center justify-end gap-1.5 sm:gap-2">
                                    @auth
                                        @if($isMyShare && $share->status == 'unpaid' && !$isViewerPayer)
                                            <button type="button" onclick="document.getElementById('modal-{{ $share->id }}').classList.remove('hidden')" class="px-3.5 py-1.5 rounded-full text-white text-xs font-bold btn-primary">
                                                Konfirmasi Bayar
                                            </button>

                                            <!-- Modal Upload Bukti -->
                                            <div id="modal-{{ $share->id }}" class="hidden fixed inset-0 z-50 bg-slate-900/60 flex items-center justify-center p-4">
                                                <div class="bg-white border border-slate-200 rounded-2xl max-w-md w-full p-6 text-left">
                                                    <h4 class="text-base font-bold text-slate-900 mb-2">Konfirmasi Pembayaran ke {{ $bill->payer->name }}</h4>
                                                    <p class="text-xs text-slate-500 mb-4">
                                                        Transfer senilai <strong>Rp {{ number_format($share->amount, 0, ',', '.') }}</strong> ke rekening {{ $bill->payer->name }} lalu sertakan buktinya.
                                                    </p>
                                                    <form action="{{ route('shares.pay', $share->id) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                                                        @csrf
                                                        <div>
                                                             <label class="block text-xs font-bold text-slate-700 mb-1">Bukti Transfer (Foto)</label>
                                                            <input type="file" name="proof_image" accept="image/*" class="w-full text-xs text-slate-600 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:bg-slate-100 file:text-slate-700 file:font-semibold">
                                                        </div>
                                                        <div>
                                                            <label class="block text-xs font-bold text-slate-700 mb-1">Catatan</label>
                                                            <input type="text" name="notes" placeholder="Sudah transfer ya" class="w-full px-3 py-2 rounded-lg bg-white border border-slate-300 text-slate-900 text-xs">
                                                        </div>
                                                        <div class="flex justify-end gap-2 pt-2">
                                                            <button type="button" onclick="document.getElementById('modal-{{ $share->id }}').classList.add('hidden')" class="px-4 py-2 rounded-lg bg-slate-100 text-slate-700 text-xs font-bold">Batal</button>
                                                            <button type="submit" class="px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold">Kirim Konfirmasi</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        @endif

                                        @if($share->proof_image)
                                            <a href="{{ asset('storage/' . $share->proof_image) }}" target="_blank" class="px-2.5 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold">
                                                Bukti Trf
                                            </a>
                                        @endif

                                        @if($isViewerPayer && $share->status == 'paid' && $share->user_id != Auth::id())
                                            <form action="{{ route('shares.approve', $share->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="px-3.5 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition-all">
                                                    Setujui Lunas
                                                </button>
                                            </form>
                                        @endif
                                    @else
                                        @if($share->proof_image)
                                            <a href="{{ asset('storage/' . $share->proof_image) }}" target="_blank" class="px-2.5 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold">
                                                Bukti Trf
                                            </a>
                                        @else
                                            <span class="text-xs text-slate-400">-</span>
                                        @endif
                                    @endauth
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @if($bill->invoice_image)
        <script>
            function viewInvoice() {
                const img = document.getElementById('invoiceViewerImage');
                if (!img) return;

                if (window.Viewer) {
                    const viewer = new window.Viewer(img, {
                        hidden: function() {
                            viewer.destroy();
                        },
                        navbar: false,
                        title: false,
                        toolbar: {
                            zoomIn: 1,
                            zoomOut: 1,
                            oneToOne: 1,
                            reset: 1,
                            prev: 0,
                            play: 0,
                            next: 0,
                            rotateLeft: 1,
                            rotateRight: 1,
                            flipHorizontal: 1,
                            flipVertical: 1,
                        },
                    });
                    viewer.show();
                } else {
                    console.error('Viewer.js not loaded');
                }
            }
        </script>
    @endif
</div>
@endsection

