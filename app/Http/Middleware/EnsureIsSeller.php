<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureIsSeller
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            return redirect()->route('login')->withErrors([
                'email' => 'Silakan masuk dulu untuk mengelola produk.',
            ]);
        }

        if (! Auth::user()->canSell()) {
            return redirect()->route('dashboard')->with(
                'error',
                'Halaman khusus penjual. Akunmu terdaftar sebagai pembeli.'
            );
        }

        return $next($request);
    }
}
