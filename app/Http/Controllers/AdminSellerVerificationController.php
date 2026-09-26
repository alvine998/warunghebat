<?php

namespace App\Http\Controllers;

use App\Mail\WarungApprovedMail;
use App\Mail\WarungRejectedMail;
use App\Models\SellerVerification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

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

        $verification->loadMissing('user.store');

        if ($verification->user) {
            try {
                Mail::to($verification->user->email)->send(new WarungApprovedMail($verification->user, $verification));
            } catch (Throwable $e) {
                Log::warning('Warung approved email failed for '.$verification->user->email.': '.$e->getMessage());
            }
        }

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

        $verification->loadMissing('user.store');

        if ($verification->user) {
            try {
                Mail::to($verification->user->email)->send(new WarungRejectedMail($verification->user, $verification->fresh('user.store')));
            } catch (Throwable $e) {
                Log::warning('Warung rejected email failed for '.$verification->user->email.': '.$e->getMessage());
            }
        }

        return back()->with('success', "KYC {$verification->full_name} ditolak dengan alasan.");
    }
}
