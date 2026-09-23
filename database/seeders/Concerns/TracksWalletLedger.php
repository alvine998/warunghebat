<?php

namespace Database\Seeders\Concerns;

use App\Models\Wallet;
use Illuminate\Database\Eloquent\Model;

trait TracksWalletLedger
{
    /**
     * Whether this wallet already recorded the given movement for the reference.
     *
     * Seeders use it so re-running `db:seed` never credits or debits twice for the
     * same order or withdrawal, matching the table's unique reference index.
     */
    protected function ledgerEntryExists(Wallet $wallet, Model $reference, string $type): bool
    {
        return $wallet->transactions()
            ->where('type', $type)
            ->where('reference_type', $reference->getMorphClass())
            ->where('reference_id', $reference->getKey())
            ->exists();
    }
}
