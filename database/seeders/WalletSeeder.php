<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\Store;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Database\Seeders\Concerns\TracksWalletLedger;
use Illuminate\Database\Seeder;

class WalletSeeder extends Seeder
{
    use TracksWalletLedger;

    /**
     * Gives every store a wallet and releases escrow for its completed orders.
     *
     * Completed orders are credited exactly once: an order that already has a
     * credit in the ledger is skipped, so this can run on every `db:seed`.
     */
    public function run(): void
    {
        foreach (Store::all() as $store) {
            $wallet = Wallet::forStore($store);

            $completed = $store->orders()->where('status', Order::STATUS_COMPLETED)->get();

            foreach ($completed as $order) {
                if ($this->ledgerEntryExists($wallet, $order, WalletTransaction::TYPE_CREDIT)) {
                    continue;
                }

                $transaction = $wallet->credit(
                    $order->total - $order->commission_amount,
                    "Pesanan {$order->code()} — {$store->name}",
                    $order,
                );

                $transaction->created_at = $order->completed_at ?? now();
                $transaction->save();
            }
        }
    }
}
