<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;

class AuthController extends Controller
{
    /** Where to send a user after auth, based on role. */
    protected function redirectFor(User $user): string
    {
        return $user->isAdmin()
            ? route('admin.dashboard')
            : route('dashboard');
    }

    // ---------- USER SIDE ----------

    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->to($this->redirectFor(Auth::user()));
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        if (Auth::check()) {
            return redirect()->to($this->redirectFor(Auth::user()));
        }

        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'password.required' => 'Kata sandi wajib diisi.',
        ]);

        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            return redirect()->intended($this->redirectFor(Auth::user()));
        }

        return back()->withErrors([
            'email' => 'Email atau kata sandi salah. Coba lagi ya.',
        ])->onlyInput('email');
    }

    public function showRegister()
    {
        if (Auth::check()) {
            return redirect()->to($this->redirectFor(Auth::user()));
        }

        return view('auth.register');
    }

    public function register(Request $request)
    {
        if (Auth::check()) {
            return redirect()->to($this->redirectFor(Auth::user()));
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', PasswordRule::min(8)],
            'role' => ['nullable', 'in:pembeli,penjual'],
        ], [
            'name.required' => 'Nama wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.unique' => 'Email ini sudah terdaftar. Silakan masuk.',
            'password.required' => 'Kata sandi wajib diisi.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
            'password.min' => 'Kata sandi minimal 8 karakter.',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'role' => $validated['role'] ?? 'pembeli',
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard')->with('success', 'Selamat datang di Warung Hebat, '.$user->name.'!');
    }

    // ---------- ADMIN BACKOFFICE SIDE ----------

    public function showAdminLogin()
    {
        if (Auth::check()) {
            return redirect()->to($this->redirectFor(Auth::user()));
        }

        return view('auth.admin-login');
    }

    public function adminLogin(Request $request)
    {
        // Already signed in? Admins go straight to backoffice,
        // non-admins get a fresh session before attempting admin creds.
        if (Auth::check()) {
            if (Auth::user()->isAdmin()) {
                return redirect()->route('admin.dashboard');
            }
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ], [
            'email.required' => 'Email admin wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'password.required' => 'Kata sandi wajib diisi.',
        ]);

        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            /** @var User $user */
            $user = Auth::user();

            if (! $user->isAdmin()) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('admin.login')->withErrors([
                    'email' => 'Akun ini bukan admin. Gunakan halaman masuk pengguna.',
                ])->onlyInput('email');
            }

            $request->session()->regenerate();

            return redirect()->intended(route('admin.dashboard'));
        }

        return back()->withErrors([
            'email' => 'Email atau kata sandi admin salah.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }

    // ---------- LUPA KATA SANDI ----------

    public function showForgotPassword()
    {
        if (Auth::check()) {
            return redirect()->to($this->redirectFor(Auth::user()));
        }

        return view('auth.forgot-password');
    }

    public function sendResetLink(Request $request)
    {
        if (Auth::check()) {
            return redirect()->to($this->redirectFor(Auth::user()));
        }

        $request->validate([
            'email' => ['required', 'email'],
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
        ]);

        $status = Password::sendResetLink($request->only('email'));

        return $status === Password::RESET_LINK_SENT
            ? back()->with('success', 'Tautan reset sudah dikirim! Cek email kamu (termasuk folder spam) — tautannya berlaku 60 menit.')
            : back()->withErrors(['email' => 'Email ini tidak terdaftar. Cek lagi atau daftar akun baru.'])->onlyInput('email');
    }

    public function showResetPassword(string $token)
    {
        if (Auth::check()) {
            return redirect()->to($this->redirectFor(Auth::user()));
        }

        return view('auth.reset-password', [
            'token' => $token,
            'email' => request()->query('email', ''),
        ]);
    }

    public function resetPassword(Request $request)
    {
        if (Auth::check()) {
            return redirect()->to($this->redirectFor(Auth::user()));
        }

        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', PasswordRule::min(8)],
        ], [
            'token.required' => 'Tautan reset tidak valid. Minta tautan baru.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'password.required' => 'Kata sandi baru wajib diisi.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
            'password.min' => 'Kata sandi minimal 8 karakter.',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password): void {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('success', 'Kata sandi berhasil diubah! Masuk dengan kata sandi barumu.')
            : back()->withErrors(['email' => 'Tautan reset kedaluwarsa atau tidak valid. Minta tautan baru.'])->onlyInput('email');
    }
}
