<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureIsPenjual
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            return redirect()->route('login')->withErrors([
                'email' => 'Silakan masuk dulu untuk mengelola warung.',
            ]);
        }

        if (Auth::user()->role !== 'penjual') {
            return redirect()->route('dashboard')->with(
                'error',
                'Pengaturan warung hanya tersedia untuk akun penjual.'
            );
        }

        return $next($request);
    }
}
