@extends('layouts.main')

@section('title', 'Profil Pengguna & Rekening')

@section('main-content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-2.5 sm:gap-3">
            <a href="{{ route('dashboard') }}" class="inline-flex items-center justify-center gap-2 px-3 py-2 sm:px-4 sm:py-2 rounded-xl bg-white border border-slate-200 text-xs font-bold text-slate-700 hover:text-slate-900 hover:bg-slate-50 transition-colors flex-shrink-0" title="Kembali ke Dashboard">
                <i class="fa-solid fa-arrow-left-long"></i>
                <span class="hidden sm:inline">Dashboard</span>
            </a>
            <h1 class="text-base sm:text-xl font-bold text-slate-900 tracking-tight leading-tight">
                Pengaturan Profil & Rekening Korban
            </h1>
        </div>
    </div>


    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-stretch">
        
        <!-- CARD 1: INFORMASI REKENING BANK / E-WALLET -->
        <div class="rounded-2xl bg-white border border-slate-200 p-6 flex flex-col justify-between h-full">
            <div class="space-y-5">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                        <i data-lucide="credit-card" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-slate-900">Rekening Tujuan Transfer</h2>
                        <p class="text-xs text-slate-500 font-medium">Biar temen lu tau mau balikin duit ke mana (jangan sampe salah no rek!)</p>
                    </div>
                </div>

                <form id="paymentMethodForm" action="{{ route('profile.payment.update') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Nama Bank / E-Wallet</label>
                        <input type="text" name="type" value="{{ old('type', $paymentMethod->type ?? 'BCA') }}" required placeholder="Contoh: BCA, Mandiri, BRI, GoPay, Dana" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-700 text-xs font-normal focus:bg-white focus:border-indigo-500 focus:outline-none transition-all">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Nomor Rekening / No. HP E-Wallet</label>
                        <input type="text" name="account_number" value="{{ old('account_number', $paymentMethod->account_number ?? '') }}" required placeholder="Contoh: 1234567890" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-700 text-xs font-normal focus:bg-white focus:border-indigo-500 focus:outline-none transition-all">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Atas Nama (A/N)</label>
                        <input type="text" name="account_name" value="{{ old('account_name', $paymentMethod->account_name ?? $user->name) }}" required placeholder="Contoh: {{ $user->name }}" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-700 text-xs font-normal focus:bg-white focus:border-indigo-500 focus:outline-none transition-all">
                    </div>

                    @if($paymentMethod && $paymentMethod->qr_code_image)
                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-between">
                            <span class="text-xs font-normal text-slate-700">QRIS Tersimpan:</span>
                            <a href="{{ asset('storage/' . $paymentMethod->qr_code_image) }}" target="_blank" class="text-xs text-indigo-600 hover:underline font-bold">
                                Lihat Gambar
                            </a>
                        </div>
                    @endif

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Upload QRIS (Biar yang males ngetik tinggal scan)</label>
                        <input type="file" name="qr_code_image" accept="image/*" class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-normal file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200">
                    </div>
                </form>
            </div>

            <div class="pt-5 mt-4 border-t border-slate-100">
                <button type="submit" form="paymentMethodForm" class="w-full py-2.5 px-4 rounded-full font-bold text-xs btn-primary">
                    Simpan Rekening Buat Nampung Duit
                </button>
            </div>
        </div>

        <!-- CARD 2: INFORMASI AKUN & GANTI PASSWORD -->
        <div class="rounded-2xl bg-white border border-slate-200 p-6 flex flex-col justify-between h-full">
            <div class="space-y-5">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center">
                        <i data-lucide="user-cog" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-slate-900">Data Akun Warga Kontrakan</h2>
                        <p class="text-xs text-slate-500 font-normal">Identitas resmi lu di kontrakan ini.</p>
                    </div>
                </div>

                <form id="profileForm" action="{{ route('profile.update') }}" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Nomor Induk Kewarasan (NIK)</label>
                        <input type="text" value="{{ $user->nik }}" disabled class="w-full px-3.5 py-2.5 rounded-xl bg-slate-100 border border-slate-200 text-slate-600 text-xs font-normal cursor-not-allowed">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Nama Lengkap</label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-700 text-xs font-normal focus:bg-white focus:border-indigo-500 focus:outline-none transition-all">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Email (Opsional)</label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" placeholder="nama@email.com" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-700 text-xs font-normal focus:bg-white focus:border-indigo-500 focus:outline-none transition-all">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Nomor WhatsApp / HP (Biar gampang ditagih utang)</label>
                        <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="08xxxxxxxxxx" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-700 text-xs font-normal focus:bg-white focus:border-indigo-500 focus:outline-none transition-all">
                    </div>

                    <!-- Form Ganti Password (Langsung Ditampilkan) -->
                    <div class="pt-3 border-t border-slate-100 space-y-3">
                        <div class="flex items-center gap-2">
                            <i data-lucide="key-round" class="w-4 h-4 text-slate-500"></i>
                            <span class="text-xs font-bold text-slate-800">Ganti Password (Kosongkan kalau gamau ribet)</span>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Password Saat Ini</label>
                            <input type="password" name="current_password" placeholder="Masukkan password lama" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-700 text-xs font-normal focus:bg-white focus:border-indigo-500 focus:outline-none transition-all">
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1.5">Password Baru</label>
                                <input type="password" name="password" placeholder="Min. 6 karakter" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-700 text-xs font-normal focus:bg-white focus:border-indigo-500 focus:outline-none transition-all">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1.5">Konfirmasi Password Baru</label>
                                <input type="password" name="password_confirmation" placeholder="Ulangi password baru" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-700 text-xs font-normal focus:bg-white focus:border-indigo-500 focus:outline-none transition-all">
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div class="pt-5 mt-4 border-t border-slate-100">
                <button type="submit" form="profileForm" class="w-full py-2.5 px-4 rounded-full font-bold text-xs btn-primary">
                    Simpan Perubahan Akun
                </button>
            </div>
        </div>

    </div>
</div>
@endsection
