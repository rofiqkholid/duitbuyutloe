@extends('layouts.app')

@section('title', 'Dashboard Iuran')

@section('content')
<!-- Welcome Banner (Light Mode) -->
<div class="relative overflow-hidden rounded-2xl bg-white border-primary-gradient p-5 sm:p-8 mb-6 sm:mb-8">
    <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-5 sm:gap-6">
        <div>
            @auth
                <h2 class="text-xl sm:text-3xl font-semibold text-slate-900 tracking-tight">
                    Halo, {{ $currentUser->name }}! 👋
                </h2>
                <p class="text-xs sm:text-base text-slate-600 mt-2 max-w-2xl leading-relaxed">
                    Setiap faktur yang diupload otomatis dibagi rata <strong>5 penghuni</strong>. Yang nalangin duluan langsung lunas, sisanya wajib sadar diri buat transfer.
                </p>
            @else
                <h2 class="text-xl sm:text-3xl font-semibold text-slate-900 tracking-tight">
                    Duit Buyut Loe 🏠
                </h2>
                <p class="text-xs sm:text-base text-slate-600 mt-2 max-w-2xl leading-relaxed">
                    Biar nggak ada yang pura-pura lupa ingatan pas tagihan wifi, listrik, air, & uang kang sampah nongol. Emang dikira duit buyut loe yang bayarin? Login sekarang, bayar utang loe!
                </p>
            @endauth
        </div>
        @auth
            <div class="flex-shrink-0">
                <a href="{{ route('bills.create') }}" class="inline-flex items-center justify-center gap-2 w-full sm:w-auto px-5 py-2.5 sm:py-3 rounded-full text-xs sm:text-sm font-semibold btn-primary">
                    <i data-lucide="receipt" class="w-4 h-4 sm:w-5 sm:h-5"></i>
                    <span>Upload Faktur Tagihan</span>
                </a>
            </div>
        @endauth
    </div>
</div>

<!-- 4 Financial Stats Cards -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-6 mb-6 sm:mb-8">
    @auth
        <!-- Hutang Saya -->
        <div class="rounded-2xl bg-white border border-slate-200 p-4 sm:p-5 hover:border-rose-300 transition-colors">
            <div class="flex items-center justify-between mb-2 sm:mb-3">
                <span class="text-xs font-medium text-slate-500">Dosa masa lalu (Utang lu)</span>
                <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center">
                    <i data-lucide="arrow-up-right" class="w-4 h-4 sm:w-5 sm:h-5"></i>
                </div>
            </div>
            <div class="text-xl sm:text-3xl font-semibold text-rose-600">
                Rp {{ number_format($totalMyDebt, 0, ',', '.') }}
            </div>
            <p class="text-[11px] sm:text-xs text-slate-500 mt-1 sm:mt-2 font-medium">
                {{ $myUnpaidShares->where('status', 'unpaid')->count() }} tagihan belum lu bayar, jangan amnesia
            </p>
        </div>

        <!-- Piutang Saya (Yang Ditalangi) -->
        <div class="rounded-2xl bg-white border border-slate-200 p-4 sm:p-5 hover:border-emerald-300 transition-colors">
            <div class="flex items-center justify-between mb-2 sm:mb-3">
                <span class="text-xs font-medium text-slate-500">Duit lu yang disandera</span>
                <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <i data-lucide="arrow-down-left" class="w-4 h-4 sm:w-5 sm:h-5"></i>
                </div>
            </div>
            <div class="text-xl sm:text-3xl font-semibold text-emerald-600">
                Rp {{ number_format($totalToReceive, 0, ',', '.') }}
            </div>
            <p class="text-[11px] sm:text-xs text-slate-500 mt-1 sm:mt-2 font-medium">
                Wajib ditagih sebelum mereka pura-pura miskin
            </p>
        </div>
    @else
        <!-- Uang Deposit Rumah (Saat Belum Login) -->
        <div class="rounded-2xl bg-white border border-slate-200 p-4 sm:p-5 hover:border-amber-300 transition-colors">
            <div class="flex items-center justify-between mb-2 sm:mb-3">
                <span class="text-xs font-medium text-slate-500">Uang Deposit Rumah</span>
                <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                    <i data-lucide="shield-alert" class="w-4 h-4 sm:w-5 sm:h-5"></i>
                </div>
            </div>
            <div class="text-xl sm:text-3xl font-semibold text-amber-600">
                Rp 3.000.000
            </div>
            <p class="text-[11px] sm:text-xs text-slate-500 mt-1 sm:mt-2 font-medium">
                Uang jaminan yang nyangkut di ibu kontrakan
            </p>
        </div>

        <!-- Utang Tertinggi Siapa (Saat Belum Login) -->
        <div class="rounded-2xl bg-white border border-slate-200 p-4 sm:p-5 hover:border-rose-300 transition-colors">
            <div class="flex items-center justify-between mb-2 sm:mb-3">
                <span class="text-xs font-medium text-slate-500">Utang tertinggi siapa</span>
                <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center">
                    <i data-lucide="crown" class="w-4 h-4 sm:w-5 sm:h-5"></i>
                </div>
            </div>
            <div class="text-xl sm:text-3xl font-semibold text-rose-600">
                Rp {{ number_format($highestDebtorAmount, 0, ',', '.') }}
            </div>
            <p class="text-[11px] sm:text-xs text-slate-500 mt-1 sm:mt-2 font-medium">
                @if($highestDebtorAmount > 0)
                    <strong class="text-slate-800">{{ $highestDebtorName }}</strong> lu kira rumah nenek moyang lu? Login & bayar!
                @else
                    Aman, gaada yang nunggak utang!
                @endif
            </p>
        </div>
    @endauth

    <!-- Total Biaya Bersama -->
    <div class="rounded-2xl bg-white border border-slate-200 p-4 sm:p-5 hover:border-indigo-300 transition-colors">
        <div class="flex items-center justify-between mb-2 sm:mb-3">
            <span class="text-xs font-medium text-slate-500">Total duit melayang</span>
            <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                <i data-lucide="wallet" class="w-4 h-4 sm:w-5 sm:h-5"></i>
            </div>
        </div>
        <div class="text-xl sm:text-3xl font-semibold text-slate-900">
            Rp {{ number_format($totalKontrakanBills, 0, ',', '.') }}
        </div>
        <p class="text-[11px] sm:text-xs text-slate-500 mt-1 sm:mt-2 font-medium">Pengeluaran nyata, bukan dibayarin duit buyut loe</p>
    </div>

    <!-- 5 Anggota -->
    <div class="rounded-2xl bg-white border border-slate-200 p-4 sm:p-5 hover:border-cyan-300 transition-colors">
        <div class="flex items-center justify-between mb-2 sm:mb-3">
            <span class="text-xs font-medium text-slate-500">Beban bersama</span>
            <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-xl bg-cyan-50 text-cyan-600 flex items-center justify-center">
                <i data-lucide="users" class="w-4 h-4 sm:w-5 sm:h-5"></i>
            </div>
        </div>
        <div class="text-xl sm:text-3xl font-semibold text-slate-900">
            Ada 5 Pangeran
        </div>
        <p class="text-[11px] sm:text-xs text-slate-500 mt-1 sm:mt-2 font-medium">Patungan 20% rata, gaada ceritanya numpang gratis</p>
    </div>
</div>

<!-- Layout Full-Width Stack (Tabel Full di Atas, Rekening & Piutang di Bawah) -->
<div class="space-y-8">

    @auth
        <!-- TAGIHAN SAYA YANG HARUS DIBAYAR (FULL WIDTH) -->
        <div class="rounded-2xl bg-white border border-slate-200 p-4 sm:p-6">
            <div class="flex items-center justify-between mb-4 sm:mb-5">
                <h3 class="text-base sm:text-lg font-bold text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-bell text-rose-500"></i>
                    Beban hidup lu (cepetan transfer!)
                </h3>
            </div>

            @if($myUnpaidShares->isEmpty())
                <div class="text-center py-8 sm:py-10 text-slate-500">
                    <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-full bg-emerald-50 text-emerald-600 mx-auto flex items-center justify-center mb-3">
                        <i data-lucide="check-check" class="w-5 h-5 sm:w-6 sm:h-6"></i>
                    </div>
                    <p class="font-bold text-sm sm:text-base text-slate-800">Aman! Lu lagi jadi warga teladan 🎉</p>
                    <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">Gaada utang yang nunggak. Pertahankan gengsi dan kewarasan lu ini.</p>
                </div>
            @else
                <div class="overflow-x-auto -mx-4 sm:mx-0 px-4 sm:px-0">
                    <table class="w-full text-left text-xs sm:text-sm whitespace-nowrap sm:whitespace-normal">
                        <thead class="text-xs text-slate-500 bg-slate-50 border-b border-slate-200">
                            <tr>
                                <th class="py-2.5 sm:py-3 px-3 sm:px-4 font-bold">Tagihan</th>
                                <th class="py-2.5 sm:py-3 px-3 sm:px-4 font-bold">Tujuan transfer</th>
                                <th class="py-2.5 sm:py-3 px-3 sm:px-4 font-bold">Rekening / Bank</th>
                                <th class="py-2.5 sm:py-3 px-3 sm:px-4 font-bold">Bagian kamu</th>
                                <th class="py-2.5 sm:py-3 px-3 sm:px-4 font-bold">Status</th>
                                <th class="py-2.5 sm:py-3 px-3 sm:px-4 font-bold text-left">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($myUnpaidShares as $share)
                                <tr class="hover:bg-slate-50/80 transition-colors">
                                    <td class="py-3 px-3 sm:px-4">
                                        <div class="font-bold text-slate-900">{{ $share->bill->title }}</div>
                                        <div class="text-[11px] sm:text-xs text-slate-500">Total faktur: Rp {{ number_format($share->bill->total_amount, 0, ',', '.') }}</div>
                                    </td>
                                    <td class="py-3 px-3 sm:px-4">
                                        <span class="text-slate-800 text-xs sm:text-sm font-normal">{{ $share->bill->payer->name }}</span>
                                    </td>
                                    <td class="py-3 px-3 sm:px-4">
                                        <div class="text-xs text-slate-700 font-medium">
                                            {{ $share->bill->payer->paymentMethods->first()?->type ?? 'Transfer' }}
                                        </div>
                                        <div class="text-[11px] sm:text-xs text-slate-500 font-normal">
                                            {{ $share->bill->payer->paymentMethods->first()?->account_number ?? '-' }}
                                        </div>
                                    </td>
                                    <td class="py-3 px-3 sm:px-4">
                                        <div class="text-slate-800 text-xs sm:text-sm font-normal">
                                            Rp {{ number_format($share->amount, 0, ',', '.') }}
                                        </div>
                                    </td>
                                    <td class="py-3 px-3 sm:px-4">
                                        @if($share->status == 'unpaid')
                                            <span class="text-xs font-medium text-slate-500">
                                                Belum bayar
                                            </span>
                                        @elseif($share->status == 'paid')
                                            <span class="text-xs font-semibold text-slate-700">
                                                Menunggu cek
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-3 sm:px-4 text-left">
                                        <a href="{{ route('bills.show', $share->bill->id) }}" class="text-xs font-semibold text-slate-600 hover:text-slate-900 px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 transition-colors">
                                            Bayar
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    @endauth

    <!-- DAFTAR FAKTUR KONTRAKAN (FULL WIDTH) -->
    <div class="rounded-2xl bg-white border border-slate-200 p-4 sm:p-6">
        <div class="flex items-center justify-between mb-4 sm:mb-5">
            <div>
                <h3 class="text-base sm:text-lg font-bold text-slate-900 flex items-center gap-2 sm:gap-2.5">
                    <i class="fa-solid fa-coins text-amber-500"></i>
                    <span>Bukti dosa & bon pengeluaran</span>
                </h3>
                <p class="text-xs text-slate-500 mt-1">
                    Pangeran yg muncul disini kece-kece!
                </p>
            </div>
            <a href="{{ route('bills.index') }}" class="text-xs text-indigo-600 hover:text-indigo-700 font-bold flex items-center gap-1 flex-shrink-0 p-1 sm:p-0" title="Lihat semua faktur">
                <span class="hidden sm:inline">Lihat semua</span>
                <i data-lucide="chevron-right" class="w-4 h-4"></i>
            </a>
        </div>

        <div class="overflow-x-auto -mx-4 sm:mx-0 px-4 sm:px-0">
            <table class="w-full text-left text-xs sm:text-sm whitespace-nowrap sm:whitespace-normal">
                <thead class="text-xs text-slate-500 bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="py-2.5 sm:py-3 px-3 sm:px-4 font-bold">Type</th>
                        <th class="py-2.5 sm:py-3 px-3 sm:px-4 font-bold">Faktur</th>
                        <th class="py-2.5 sm:py-3 px-3 sm:px-4 font-bold">Ditalangi</th>
                        <th class="py-2.5 sm:py-3 px-3 sm:px-4 font-bold">Total</th>
                        <th class="py-2.5 sm:py-3 px-3 sm:px-4 font-bold">Bagi 5 orang</th>
                        <th class="py-2.5 sm:py-3 px-3 sm:px-4 font-bold">Tanggal</th>
                        <th class="py-2.5 sm:py-3 px-3 sm:px-4 font-bold">Detail</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($recentBills as $bill)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="py-3 px-3 sm:px-4 font-medium text-slate-600">
                                {{ $bill->type ?: $bill->category }}
                            </td>
                            <td class="py-3 px-3 sm:px-4">
                                <a href="{{ route('bills.show', $bill->id) }}" class="font-bold text-slate-900 hover:text-indigo-600 block transition-colors">
                                    {{ $bill->title }}
                                </a>
                            </td>
                            <td class="py-3 px-3 sm:px-4">
                                <span class="font-bold text-slate-800">{{ $bill->payer->name }}</span>
                            </td>
                            <td class="py-3 px-3 sm:px-4 font-semibold text-slate-900">
                                Rp {{ number_format($bill->total_amount, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-3 sm:px-4 font-semibold text-slate-900">
                                Rp {{ number_format($bill->share_amount, 0, ',', '.') }}
                                <span class="text-[11px] sm:text-xs text-slate-500 font-normal">/orang</span>
                            </td>
                            <td class="py-3 px-3 sm:px-4 text-xs text-slate-500">
                                {{ \Carbon\Carbon::parse($bill->bill_date)->format('d M Y') }}
                            </td>
                            <td class="py-3 px-3 sm:px-4">
                                <a href="{{ route('bills.show', $bill->id) }}" class="text-xs font-semibold text-slate-600 hover:text-slate-900 px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 transition-colors">
                                    Rincian
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-8 text-slate-400">
                                Belum ada faktur yang diunggah.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- BAGIAN BAWAH: Rekening 5 Penghuni & Piutang Saya -->
    <div class="grid grid-cols-1 {{ Auth::check() ? 'md:grid-cols-2' : 'w-full' }} gap-6 items-stretch">
        
        <!-- REKENING 5 PENGHUNI (POSISI DI BAWAH TABEL) -->
        <div class="rounded-2xl bg-white border border-slate-200 p-4 sm:p-6 flex flex-col h-full">
            <h3 class="text-base font-bold text-slate-900 flex items-center gap-2 mb-2 flex-shrink-0">
                <i data-lucide="credit-card" class="w-5 h-5 text-indigo-600"></i>
                <span>Rekening para korban nalangin</span>
            </h3>
            <p class="text-xs text-slate-500 mb-4 flex-shrink-0">
                Tujuan transfer buat balikin duit orang:
            </p>

            <div class="space-y-3 flex-1 overflow-y-auto max-h-[460px] pr-1">
                @foreach($users as $u)
                    @php $pm = $u->paymentMethods->first(); @endphp
                    <div class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 border border-slate-200/80">
                        <div class="w-9 h-9 rounded-full bg-slate-200 text-slate-700 text-xs font-bold flex items-center justify-center flex-shrink-0">
                            {{ substr($u->name, 0, 1) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between gap-2">
                                <span class="font-bold text-sm text-slate-900 truncate">{{ $u->name }}</span>
                                <span class="text-xs font-medium text-slate-500 flex-shrink-0">{{ $pm->type ?? 'Transfer' }}</span>
                            </div>
                            <div class="text-xs text-slate-600 font-medium mt-0.5">
                                <div class="flex flex-col sm:flex-row sm:items-center sm:gap-1.5">
                                    <span class="truncate">{{ $pm ? $pm->masked_account_number : '-' }}</span>
                                    <span class="hidden sm:inline text-slate-400">&bull;</span>
                                    <span class="truncate text-slate-500 text-[11px] sm:text-xs">a/n {{ $pm ? $pm->masked_account_name : 'XXX' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        @auth
            <!-- APPROVAL PIUTANG SAYA (JIKA LOGIN) -->
            <div class="rounded-2xl bg-white border border-slate-200 p-4 sm:p-6 flex flex-col h-full">
                <h3 class="text-base font-bold text-slate-900 flex items-center gap-2 mb-2 flex-shrink-0">
                    <i data-lucide="hand-coins" class="w-5 h-5 text-emerald-600"></i>
                    <span>Daftar debitur / yang ngutang ke lu</span>
                </h3>
                <p class="text-xs text-slate-500 mb-4 flex-shrink-0">
                    Duit lu masih nyangkut di mereka, jangan kasih kendor sampai ditransfer:
                </p>

                @if($claimsToReceive->isEmpty())
                    <div class="text-center py-6 text-slate-400 text-xs flex-1 flex items-center justify-center">
                        Dompet aman, gaada yang ngutang ke lu saat ini.
                    </div>
                @else
                    <div class="space-y-3 flex-1 overflow-y-auto max-h-[460px] pr-1 no-scrollbar">
                        @foreach($claimsToReceive as $claim)
                            <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200">
                                <div class="flex items-start justify-between gap-2">
                                    <div>
                                        <div class="font-bold text-slate-900 text-sm">{{ $claim->user->name }}</div>
                                        <div class="text-xs text-slate-500">{{ $claim->bill->title }}</div>
                                    </div>
                                    <div class="font-semibold text-emerald-600 text-sm">
                                        Rp {{ number_format($claim->amount, 0, ',', '.') }}
                                    </div>
                                </div>

                                <div class="flex items-center justify-between gap-2 mt-3 pt-2.5 border-t border-slate-200">
                                    <div>
                                        @if($claim->status == 'unpaid')
                                            <span class="text-xs font-medium text-slate-500">
                                                Belum transfer
                                            </span>
                                        @elseif($claim->status == 'paid')
                                            <span class="text-xs font-semibold text-slate-700">
                                                Sudah transfer (cek rek)
                                            </span>
                                        @endif
                                    </div>

                                    <div class="flex items-center gap-1.5">
                                        @if($claim->proof_image)
                                            <a href="{{ asset('storage/' . $claim->proof_image) }}" target="_blank" class="px-2.5 py-1 rounded bg-slate-200 text-slate-700 hover:text-slate-900 text-xs font-semibold">
                                                Bukti
                                            </a>
                                        @endif

                                        @if($claim->status == 'paid')
                                            <form action="{{ route('shares.approve', $claim->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="px-3 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition-all">
                                                    Lunas
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @endauth

    </div>

</div>
@endsection
