<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Http;

class ForgotPasswordController extends Controller
{
    public function showLinkRequestForm()
    {
        return view('auth.forgot-password');
    }

    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'nik' => 'required|string',
        ], [
            'nik.required' => 'Nomor Induk Kewarasan (NIK) wajib diisi.',
        ]);

        $nik = strtoupper(trim($request->input('nik')));
        $user = User::where('nik', $nik)->first();

        if (!$user) {
            return back()->withErrors([
                'nik' => 'NIK tidak ditemukan di sistem.',
            ])->withInput();
        }

        if (empty($user->email)) {
            return back()->withErrors([
                'nik' => 'Akun dengan NIK ini belum memiliki alamat email terdaftar. Hubungi admin.',
            ])->withInput();
        }

        // Kirim reset link menggunakan email user yang terdaftar
        $status = Password::sendResetLink(['email' => $user->email]);

        if ($status === Password::RESET_LINK_SENT) {
            // Sensor tampilan email untuk privasi (contoh: a***@dyanaf.id)
            $parts = explode('@', $user->email);
            $namePart = $parts[0];
            $domainPart = $parts[1] ?? '';
            $maskedName = strlen($namePart) > 2 
                ? substr($namePart, 0, 1) . str_repeat('*', strlen($namePart) - 2) . substr($namePart, -1)
                : $namePart . '***';
            $maskedEmail = $maskedName . '@' . $domainPart;

            return back()->with('status', "Tautan reset password berhasil dikirim ke email terdaftar Anda ({$maskedEmail}). Silakan periksa kotak masuk atau folder spam Anda.");
        }

        return back()->withErrors(['nik' => __($status)])->withInput();
    }
}
