<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Silakan masuk dulu.'], 401);
            }

            return redirect()->route('admin.login')->withErrors([
                'email' => 'Silakan masuk dengan akun admin untuk membuka backoffice.',
            ]);
        }

        if (! Auth::user()->isAdmin()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Akses ditolak. Khusus admin.'], 403);
            }

            return redirect()->route('dashboard')->with(
                'error',
                'Halaman backoffice khusus admin. Kamu diarahkan ke dashboard pengguna.'
            );
        }

        return $next($request);
    }
}
