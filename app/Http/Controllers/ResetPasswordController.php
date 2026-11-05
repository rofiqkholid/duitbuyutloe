<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Http;

class ResetPasswordController extends Controller
{
    public function showResetForm(Request $request, $token = null)
    {
        return view('auth.reset-password')->with([
            'token' => $token,
            'email' => $request->email,
        ]);
    }

    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed|min:6',
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'password.required' => 'Password baru wajib diisi.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'password.min' => 'Password minimal 6 karakter.',
        ]);

        // Verifikasi Cloudflare Turnstile jika aktif
        $turnstileSecret = config('services.turnstile.secret_key');
        if ($turnstileSecret) {
            $turnstileResponse = $request->input('cf-turnstile-response');
            if (!$turnstileResponse) {
                return back()->withErrors([
                    'email' => 'Verifikasi Cloudflare Turnstile wajib dicentang.',
                ])->withInput();
            }

            try {
                $verify = Http::asForm()->timeout(5)->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
                    'secret' => $turnstileSecret,
                    'response' => $turnstileResponse,
                    'remoteip' => $request->ip(),
                ]);

                if (!$verify->json('success')) {
                    return back()->withErrors([
                        'email' => 'Verifikasi keamanan bot gagal. Silakan coba lagi.',
                    ])->withInput();
                }
            } catch (\Exception $e) {
                // Ignore timeout
            }
        }

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('success', __($status) . ' Silakan masuk dengan password baru.')
            : back()->withErrors(['email' => __($status)])->withInput();
    }
}
