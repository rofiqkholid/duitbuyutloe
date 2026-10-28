<?php

namespace App\Http\Controllers;

use App\Models\PaymentMethod;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    /**
     * Tampilkan halaman profil pengguna & rekening
     */
    public function show()
    {
        $user = Auth::user();
        $paymentMethod = $user->paymentMethods()->first();

        return view('profile.show', compact('user', 'paymentMethod'));
    }

    /**
     * Update profil pengguna (nama, email, phone, password jika diisi)
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'nullable|email|max:150|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:30',
            'current_password' => 'nullable|required_with:password|string',
            'password' => 'nullable|string|min:6|confirmed',
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'password.min' => 'Password baru minimal 6 karakter.',
            'password.confirmed' => 'Konfirmasi password baru tidak cocok.',
        ]);

        if ($request->filled('password')) {
            if (!Hash::check($request->current_password, $user->password)) {
                return back()->withErrors(['current_password' => 'Password saat ini salah.'])->withInput();
            }
            $user->password = Hash::make($request->password);
        }

        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->save();

        return back()->with('success', 'Profil kamu berhasil diperbarui!');
    }

    /**
     * Update nomor rekening / e-wallet dari halaman profile
     */
    public function updatePaymentMethod(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'type' => 'required|string|max:50',
            'account_number' => 'required|string|max:100',
            'account_name' => 'required|string|max:150',
            'qr_code_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ], [
            'type.required' => 'Jenis bank atau e-wallet wajib diisi.',
            'account_number.required' => 'Nomor rekening atau nomor e-wallet wajib diisi.',
            'account_name.required' => 'Nama atas nama rekening wajib diisi.',
        ]);

        $payment = PaymentMethod::firstOrNew(['user_id' => $user->id]);

        $qrPath = $payment->qr_code_image;
        if ($request->hasFile('qr_code_image')) {
            if ($qrPath && Storage::disk('public')->exists($qrPath)) {
                Storage::disk('public')->delete($qrPath);
            }
            $qrPath = $request->file('qr_code_image')->store('qrcodes', 'public');
        }

        $payment->type = $request->type;
        $payment->account_number = $request->account_number;
        $payment->account_name = $request->account_name;
        $payment->qr_code_image = $qrPath;
        $payment->save();

        return back()->with('success', 'Data nomor rekening / e-wallet berhasil disimpan!');
    }
}
