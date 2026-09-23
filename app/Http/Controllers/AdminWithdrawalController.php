<?php

namespace App\Http\Controllers;

use App\Models\Withdrawal;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminWithdrawalController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->string('status')->toString();

        if ($status === 'all') {
            $status = null;
        } elseif (! in_array($status, Withdrawal::STATUSES, true)) {
            $status = Withdrawal::STATUS_PENDING;
        }

        $search = trim((string) $request->input('search', ''));
        $withdrawalId = preg_match('/^#?(\d+)$/', $search, $matches) ? (int) $matches[1] : null;

        $withdrawals = Withdrawal::with(['wallet.store', 'processor'])
            ->when($status, fn ($query) => $query->where('status', $status))
            ->when($search !== '', function ($query) use ($search, $withdrawalId): void {
                $query->where(function ($query) use ($search, $withdrawalId): void {
                    $query->where('bank_name', 'like', "%{$search}%")
                        ->orWhere('account_number', 'like', "%{$search}%")
                        ->orWhere('account_name', 'like', "%{$search}%")
                        ->orWhereHas('wallet.store', fn ($query) => $query->where('name', 'like', "%{$search}%"));

                    if ($withdrawalId) {
                        $query->orWhere('id', $withdrawalId);
                    }
                });
            })
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        $counts = [
            'all' => Withdrawal::count(),
        ];

        foreach (Withdrawal::STATUSES as $value) {
            $counts[$value] = Withdrawal::where('status', $value)->count();
        }

        return view('admin.withdrawals', [
            'withdrawals' => $withdrawals,
            'counts' => $counts,
            'status' => $status,
        ]);
    }

    /** The admin has transferred the money to the seller's account. */
    public function markPaid(Request $request, Withdrawal $withdrawal): RedirectResponse
    {
        if (! $withdrawal->isPending()) {
            return back()->with('error', 'Penarikan ini sudah diproses.');
        }

        $validated = $request->validate([
            'admin_note' => ['nullable', 'string', 'max:500'],
        ]);

        $withdrawal->update([
            'status' => Withdrawal::STATUS_PAID,
            'admin_note' => $validated['admin_note'] ?? null,
            'processed_by' => Auth::id(),
            'processed_at' => now(),
        ]);

        $amount = number_format($withdrawal->amount, 0, ',', '.');

        return back()->with('success', "Penarikan #{$withdrawal->id} (Rp {$amount}) ditandai sudah dibayar.");
    }

    /** Rejecting a request returns the held money to the store wallet. */
    public function reject(Request $request, Withdrawal $withdrawal): RedirectResponse
    {
        if (! $withdrawal->isPending()) {
            return back()->with('error', 'Penarikan ini sudah diproses.');
        }

        $validated = $request->validate([
            'admin_note' => ['required', 'string', 'max:500'],
        ], [
            'admin_note.required' => 'Alasan penolakan wajib diisi.',
        ]);

        DB::transaction(function () use ($withdrawal, $validated): void {
            $withdrawal->update([
                'status' => Withdrawal::STATUS_REJECTED,
                'admin_note' => $validated['admin_note'],
                'processed_by' => Auth::id(),
                'processed_at' => now(),
            ]);

            $withdrawal->wallet->credit(
                $withdrawal->amount,
                "Pengembalian penarikan #{$withdrawal->id}",
                $withdrawal,
            );
        });

        $amount = number_format($withdrawal->amount, 0, ',', '.');

        return back()->with('success', "Penarikan #{$withdrawal->id} ditolak dan Rp {$amount} dikembalikan ke saldo warung.");
    }
}
