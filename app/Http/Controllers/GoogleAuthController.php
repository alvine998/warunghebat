<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class GoogleAuthController extends Controller
{
    /** Send the visitor to Google's consent screen. */
    public function redirect(Request $request): RedirectResponse
    {
        if (blank(config('services.google.client_id')) || blank(config('services.google.client_secret'))) {
            return redirect()->route('login')->withErrors([
                'email' => 'Masuk dengan Google belum tersedia saat ini.',
            ]);
        }

        // Remember the role chosen on the register page so a brand-new Google
        // account is created as intended (pembeli by default).
        $role = $request->query('role');

        if (in_array($role, ['pembeli', 'penjual'], true)) {
            $request->session()->put('google_role', $role);
        } else {
            $request->session()->forget('google_role');
        }

        return Socialite::driver('google')->redirect();
    }

    /** Link an existing account by email, or register a new buyer. */
    public function callback(Request $request): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (Throwable) {
            return redirect()->route('login')->withErrors([
                'email' => 'Gagal masuk dengan Google. Coba lagi ya.',
            ]);
        }

        $email = $googleUser->getEmail();

        if (blank($email)) {
            return redirect()->route('login')->withErrors([
                'email' => 'Akun Google ini tidak membagikan email. Pakai email & kata sandi dulu ya.',
            ]);
        }

        $user = User::where('google_id', $googleUser->getId())->first()
            ?? User::where('email', $email)->first();

        // Backoffice stays behind its own login.
        if ($user?->isAdmin()) {
            return redirect()->route('login')->withErrors([
                'email' => 'Akun admin harus masuk lewat halaman backoffice.',
            ]);
        }

        if ($user) {
            if (blank($user->google_id)) {
                $user->forceFill(['google_id' => $googleUser->getId()])->save();
            }
        } else {
            $user = User::create([
                'name' => $googleUser->getName() ?: Str::before($email, '@'),
                'email' => $email,
                'google_id' => $googleUser->getId(),
                'role' => $request->session()->get('google_role', 'pembeli'),
                'email_verified_at' => now(),
            ]);
        }

        $request->session()->forget('google_role');

        Auth::login($user, remember: true);
        $request->session()->regenerate();

        return redirect()->intended($user->homeRoute());
    }
}
