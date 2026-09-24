<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureSellerVerified
{
    /**
     * Blokir total aktivitas jualan sampai KYC disetujui admin.
     * Admin lolos; pembeli tak terdampak; penjual diarahkan ke form verifikasi.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            return redirect()->route('login')->withErrors([
                'email' => 'Silakan masuk dulu untuk berjualan.',
            ]);
        }

        $user = Auth::user();

        if ($user->isAdmin()) {
            return $next($request);
        }

        if (! $user->isKycVerified()) {
            // Hindari loop saat penjual memang sedang membuka halaman verifikasi.
            if ($request->routeIs('seller.verification.*')) {
                return $next($request);
            }

            return redirect()->route('seller.verification.show')->with(
                'error',
                'Verifikasi dulu kepemilikan warungmu (KTP + foto warung) sebelum bisa jualan.'
            );
        }

        return $next($request);
    }
}
