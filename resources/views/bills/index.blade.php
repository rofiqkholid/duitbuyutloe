@extends('layouts.main')

@section('title', 'Semua Faktur Tagihan')

@section('main-content')
<div class="space-y-6">
    <!-- Top Bar -->
    <div class="flex items-center gap-2.5 sm:gap-3">
        <a href="{{ route('dashboard') }}" class="inline-flex items-center justify-center gap-2 px-3 py-2 sm:px-4 sm:py-2 rounded-xl bg-white border border-slate-200 text-xs font-bold text-slate-700 hover:text-slate-900 hover:bg-slate-50 transition-colors flex-shrink-0" title="Kembali ke Dashboard">
            <i class="fa-solid fa-arrow-left-long"></i>
            <span class="hidden sm:inline">Dashboard</span>
        </a>
    </div>

    <!-- Main Card -->
    <div class="rounded-2xl bg-white border border-slate-200 p-5 sm:p-8 space-y-6">
        <div class="flex items-start justify-between flex-wrap gap-4 pb-6 border-b border-slate-100">
            <div>
                <h1 class="text-xl sm:text-3xl font-semibold text-slate-900 tracking-tight">
                    Semua Tagihan & Pengeluaran
                </h1>
                <p class="text-xs sm:text-sm text-slate-500 font-normal mt-1.5">
                    Riwayat lengkap iuran wifi, listrik, air, galon, dll.
                </p>
            </div>
            <a href="{{ route('bills.create') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-full font-semibold text-xs sm:text-sm btn-primary">
                <i class="fa-solid fa-plus"></i>
                <span>Upload Faktur Baru</span>
            </a>
        </div>

        <div class="overflow-x-auto -mx-5 sm:mx-0 px-5 sm:px-0">
            <table class="w-full text-left text-xs sm:text-sm whitespace-nowrap sm:whitespace-normal">
                <thead class="text-xs text-slate-500 bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="py-2.5 sm:py-3 px-3 sm:px-4 font-bold">Type</th>
                        <th class="py-2.5 sm:py-3 px-3 sm:px-4 font-bold">Faktur</th>
                        <th class="py-2.5 sm:py-3 px-3 sm:px-4 font-bold">Ditalangi</th>
                        <th class="py-2.5 sm:py-3 px-3 sm:px-4 font-bold">Total</th>
                        <th class="py-2.5 sm:py-3 px-3 sm:px-4 font-bold">Porsi per orang</th>
                        <th class="py-2.5 sm:py-3 px-3 sm:px-4 font-bold">Status</th>
                        <th class="py-2.5 sm:py-3 px-3 sm:px-4 font-bold">Tanggal</th>
                        <th class="py-2.5 sm:py-3 px-3 sm:px-4 font-bold">Detail</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($bills as $bill)
                        @php
                            $paidCount = $bill->shares->where('status', 'approved')->count();
                            $totalCount = $bill->shares->count();
                        @endphp
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
                                @if(strtolower($bill->type) === 'lainnya')
                                    <span class="text-xs font-medium text-slate-700">{{ $totalCount }} orang (nominal beda)</span>
                                @else
                                    Rp {{ number_format($bill->share_amount, 0, ',', '.') }}
                                    <span class="text-[11px] sm:text-xs text-slate-500 font-normal">/orang</span>
                                @endif
                            </td>
                            <td class="py-3 px-3 sm:px-4 text-xs font-medium text-slate-700">
                                {{ $paidCount }}/{{ $totalCount }} lunas
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
                            <td colspan="8" class="text-center py-10 text-slate-400">
                                Belum ada faktur yang tercatat.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $bills->links() }}
        </div>
    </div>
</div>
@endsection
