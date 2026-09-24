<?php

namespace App\Http\Controllers;

use App\Models\SellerVerification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SellerVerificationController extends Controller
{
    /** Form KYC: NIK + nama KTP + foto KTP + selfie + foto depan warung. */
    public function show(): View
    {
        $user = Auth::user();
        $verification = $user->sellerVerification;

        return view('seller.verification.form', [
            'verification' => $verification,
            'store' => $user->store,
        ]);
    }

    /**
     * Simpan / kirim ulang berkas KYC. Setiap pengiriman kembali ke pending
     * agar admin memeriksa ulang bukti kepemilikan warung.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $existing = $user->sellerVerification;

        if ($existing?->isVerified()) {
            return back()->with('success', 'Warungmu sudah terverifikasi. Tidak perlu kirim ulang.');
        }

        $isResubmit = $existing !== null;

        $validated = $request->validate([
            'nik' => ['required', 'string', 'regex:/^[0-9]{16}$/'],
            'full_name' => ['required', 'string', 'max:120'],
            'ktp_image' => [$isResubmit ? 'nullable' : 'required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'selfie_image' => [$isResubmit ? 'nullable' : 'required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'storefront_image' => [$isResubmit ? 'nullable' : 'required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [
            'nik.required' => 'NIK wajib diisi (16 digit di KTP).',
            'nik.regex' => 'NIK harus tepat 16 digit angka.',
            'full_name.required' => 'Nama sesuai KTP wajib diisi.',
            'ktp_image.required' => 'Foto KTP wajib diunggah.',
            'selfie_image.required' => 'Foto selfie pegang KTP wajib diunggah.',
            'storefront_image.required' => 'Foto depan warung wajib diunggah sebagai bukti kepemilikan.',
            'ktp_image.image' => 'File harus berupa gambar.',
            'selfie_image.image' => 'File harus berupa gambar.',
            'storefront_image.image' => 'File harus berupa gambar.',
            'ktp_image.mimes' => 'Format foto harus JPG, PNG, atau WebP.',
            'selfie_image.mimes' => 'Format foto harus JPG, PNG, atau WebP.',
            'storefront_image.mimes' => 'Format foto harus JPG, PNG, atau WebP.',
            'ktp_image.max' => 'Ukuran foto maksimal 2MB.',
            'selfie_image.max' => 'Ukuran foto maksimal 2MB.',
            'storefront_image.max' => 'Ukuran foto maksimal 2MB.',
        ]);

        $ktpPath = $existing?->ktp_path;
        $selfiePath = $existing?->selfie_path;
        $storefrontPath = $existing?->storefront_path;

        if ($request->hasFile('ktp_image')) {
            if ($ktpPath) {
                Storage::disk('public')->delete($ktpPath);
            }
            $ktpPath = $request->file('ktp_image')->store('kyc/ktp', 'public');
        }

        if ($request->hasFile('selfie_image')) {
            if ($selfiePath) {
                Storage::disk('public')->delete($selfiePath);
            }
            $selfiePath = $request->file('selfie_image')->store('kyc/selfie', 'public');
        }

        if ($request->hasFile('storefront_image')) {
            if ($storefrontPath) {
                Storage::disk('public')->delete($storefrontPath);
            }
            $storefrontPath = $request->file('storefront_image')->store('kyc/storefront', 'public');
        }

        $user->sellerVerification()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'nik' => $validated['nik'],
                'full_name' => $validated['full_name'],
                'ktp_path' => $ktpPath,
                'selfie_path' => $selfiePath,
                'storefront_path' => $storefrontPath,
                'status' => SellerVerification::STATUS_PENDING,
                'rejection_reason' => null,
                'verified_by' => null,
                'verified_at' => null,
            ],
        );

        return redirect()->route('dashboard')->with(
            'success',
            'Berkas verifikasi terkirim. Admin akan memeriksa bukti kepemilikan warungmu (biasanya < 1x24 jam).'
        );
    }
}
