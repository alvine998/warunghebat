<?php

namespace App\Http\Controllers;

use App\Models\SellerVerification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminSellerVerificationController extends Controller
{
    /** Setujui bukti kepemilikan — penjual langsung bisa jualan. */
    public function verify(SellerVerification $verification): RedirectResponse
    {
        if (! $verification->isPending()) {
            return back()->with('error', 'Pengajuan ini sudah diproses.');
        }

        $verification->update([
            'status' => SellerVerification::STATUS_VERIFIED,
            'rejection_reason' => null,
            'verified_by' => Auth::id(),
            'verified_at' => now(),
        ]);

        return back()->with(
            'success',
            "KYC {$verification->full_name} disetujui. Warungnya sekarang bisa jualan."
        );
    }

    /** Tolak dengan alasan agar penjual bisa memperbaiki & kirim ulang. */
    public function reject(Request $request, SellerVerification $verification): RedirectResponse
    {
        if (! $verification->isPending()) {
            return back()->with('error', 'Pengajuan ini sudah diproses.');
        }

        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:1000'],
        ], [
            'rejection_reason.required' => 'Alasan penolakan wajib diisi agar penjual bisa memperbaiki.',
        ]);

        $verification->update([
            'status' => SellerVerification::STATUS_REJECTED,
            'rejection_reason' => $validated['rejection_reason'],
            'verified_by' => Auth::id(),
            'verified_at' => now(),
        ]);

        return back()->with('success', "KYC {$verification->full_name} ditolak dengan alasan.");
    }
}
