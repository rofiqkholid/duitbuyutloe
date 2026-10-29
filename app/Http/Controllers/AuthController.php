<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function showLogin(Request $request)
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        $throttleKey = 'login:ip:' . $request->ip();
        $lockoutSeconds = 0;

        if (RateLimiter::tooManyAttempts($throttleKey, 3)) {
            $lockoutSeconds = RateLimiter::availableIn($throttleKey);
        }

        return view('auth.login', compact('lockoutSeconds'));
    }

    /**
     * Throttle key berbasis IP pengguna agar tidak bisa diakali dengan gonta-ganti NIK atau reload
     */
    protected function throttleKey(Request $request): string
    {
        return 'login:ip:' . $request->ip();
    }

    public function login(Request $request)
    {
        $throttleKey = $this->throttleKey($request);

        // 1. Cek apakah IP pengguna sedang terkena hukuman lockout/timer
        if (RateLimiter::tooManyAttempts($throttleKey, 3)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return back()->withErrors([
                'nik' => "Sedang dibatasi! Silakan tunggu {$seconds} detik lagi sebelum mencoba login.",
            ])->with('lockout_seconds', $seconds)->onlyInput('nik');
        }

        $credentials = $request->validate([
            'nik' => 'required|string',
            'password' => 'required|string',
        ], [
            'nik.required' => 'Nomor Induk Kewarasan (NIK) wajib diisi',
            'password.required' => 'Password wajib diisi',
        ]);

        // Coba autentikasi
        if (Auth::attempt(['nik' => strtoupper(trim($credentials['nik'])), 'password' => $credentials['password']], $request->filled('remember'))) {
            // Berhasil login: bersihkan rate limiter dan stage
            RateLimiter::clear($throttleKey);
            RateLimiter::clear($throttleKey . ':stage');
            $request->session()->regenerate();

            return redirect()->intended(route('dashboard'))->with('success', 'Selamat datang kembali, ' . Auth::user()->name . '!');
        }

        // 2. Autentikasi gagal: Catat hit ke RateLimiter (default window 5 menit)
        $attempts = RateLimiter::hit($throttleKey, 300);

        // Jika sudah mencapai 3 kali salah
        if ($attempts >= 3) {
            // Lacak stage hukuman di cache (stage 1 = 15 detik, stage 2 dst = 60 detik)
            $stageKey = $throttleKey . ':stage';
            $currentStage = (int) RateLimiter::attempts($stageKey);

            $decaySeconds = ($currentStage === 0) ? 15 : 60;

            // Naikkan stage agar timeout berikutnya jadi 1 menit
            RateLimiter::hit($stageKey, 3600); // simpan stage selama 1 jam

            // Set lock timer di RateLimiter
            RateLimiter::clear($throttleKey);
            // Isi 3 attempts dengan durasi hukuman decaySeconds
            for ($i = 0; $i < 3; $i++) {
                RateLimiter::hit($throttleKey, $decaySeconds);
            }

            $timeText = ($decaySeconds === 15) ? '15 detik' : '1 menit';
            return back()->withErrors([
                'nik' => "Salah 3 kali! Silakan tunggu {$timeText} sebelum mencoba kembali.",
            ])->with('lockout_seconds', $decaySeconds)->onlyInput('nik');
        }

        $sisaPercobaan = 3 - $attempts;
        return back()->withErrors([
            'nik' => "Nomor Induk Kewarasan atau password salah. Sisa percobaan: {$sisaPercobaan}x.",
        ])->onlyInput('nik');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Berhasil kabur (logout). Jangan lupa balik buat bayar utang!');
    }
}
