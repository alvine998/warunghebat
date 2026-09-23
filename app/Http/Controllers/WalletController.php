<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Setting;
use App\Models\Store;
use App\Models\Wallet;
use App\Models\Withdrawal;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class WalletController extends Controller
{
    public function index(): View
    {
        $store = Store::resolveFor(Auth::user());
        $wallet = Wallet::forStore($store);

        return view('seller.wallet.index', [
            'store' => $store,
            'wallet' => $wallet,
            // Paid orders whose money the platform still holds.
            'held' => (int) $store->orders()->where('status', Order::STATUS_PAID)->sum('total'),
            'transactions' => $wallet->transactions()->paginate(15),
            'withdrawals' => $wallet->withdrawals()->take(10)->get(),
            'lastWithdrawal' => $wallet->withdrawals()->first(),
            'min' => Setting::withdrawalMin(),
            'max' => Setting::withdrawalMax(),
        ]);
    }

    /** A withdrawal request holds the money straight away, then admin pays it out. */
    public function store(Request $request): RedirectResponse
    {
        // The input is shown with thousand separators ("50.000"); keep only digits.
        if ($request->filled('amount')) {
            $request->merge(['amount' => preg_replace('/\D/', '', (string) $request->input('amount'))]);
        }

        $min = Setting::withdrawalMin();
        $max = Setting::withdrawalMax();

        $validated = $request->validate([
            'amount' => ['required', 'integer', 'min:'.$min, 'max:'.$max],
            'bank_name' => ['required', 'string', 'max:80'],
            'account_number' => ['required', 'string', 'max:50'],
            'account_name' => ['required', 'string', 'max:80'],
            'store_note' => ['nullable', 'string', 'max:255'],
        ], [
            'amount.required' => 'Nominal penarikan wajib diisi.',
            'amount.integer' => 'Nominal penarikan tidak valid.',
            'amount.min' => 'Penarikan minimal Rp '.number_format($min, 0, ',', '.').'.',
            'amount.max' => 'Penarikan maksimal Rp '.number_format($max, 0, ',', '.').'.',
            'bank_name.required' => 'Nama bank atau e-wallet wajib diisi.',
            'account_number.required' => 'Nomor rekening wajib diisi.',
            'account_name.required' => 'Nama pemilik rekening wajib diisi.',
        ]);

        $wallet = Wallet::forStore(Store::resolveFor(Auth::user()));

        if (! $wallet->hasBalance($validated['amount'])) {
            throw ValidationException::withMessages([
                'amount' => 'Saldo warung tidak cukup untuk penarikan ini.',
            ]);
        }

        DB::transaction(function () use ($wallet, $validated): void {
            $withdrawal = $wallet->withdrawals()->create([
                'amount' => $validated['amount'],
                'status' => Withdrawal::STATUS_PENDING,
                'bank_name' => $validated['bank_name'],
                'account_number' => $validated['account_number'],
                'account_name' => $validated['account_name'],
                'store_note' => $validated['store_note'] ?? null,
            ]);

            $wallet->debit($validated['amount'], "Penarikan #{$withdrawal->id}", $withdrawal);
        });

        return back()->with('success', 'Permintaan penarikan dikirim. Dana ditahan sampai admin memprosesnya.');
    }
}
