<?php

namespace Database\Seeders;

use App\Models\Store;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Models\Withdrawal;
use Database\Seeders\Concerns\TracksWalletLedger;
use Illuminate\Database\Seeder;

class WithdrawalSeeder extends Seeder
{
    use TracksWalletLedger;

    /**
     * One request per payout state: waiting for the admin, already paid, and
     * rejected with the money returned to the wallet.
     */
    public function run(): void
    {
        $admin = User::where('email', 'admin@warunghebat.id')->first();

        foreach ($this->requests() as $data) {
            $store = Store::where('slug', $data['store'])->first();

            if (! $store) {
                continue;
            }

            $wallet = Wallet::forStore($store);

            if ($wallet->withdrawals()->where('amount', $data['amount'])->where('status', $data['status'])->exists()) {
                continue;
            }

            $requestedAt = $data['days_ago'] === 0 ? now()->subHours(3) : now()->subDays($data['days_ago']);
            $handled = $data['status'] !== Withdrawal::STATUS_PENDING;
            $handledAt = $requestedAt->copy()->addHours(6);

            $withdrawal = $wallet->withdrawals()->create([
                'amount' => $data['amount'],
                'status' => $data['status'],
                'bank_name' => $data['bank_name'],
                'account_number' => $data['account_number'],
                'account_name' => $data['account_name'],
                'store_note' => $data['store_note'],
                'admin_note' => $data['admin_note'],
                'processed_by' => $handled ? $admin?->id : null,
                'processed_at' => $handled ? $handledAt : null,
            ]);

            $withdrawal->created_at = $requestedAt;
            $withdrawal->updated_at = $handled ? $handledAt : $requestedAt;
            $withdrawal->save();

            // Requesting holds the money immediately; rejecting gives it back.
            if (! $this->ledgerEntryExists($wallet, $withdrawal, WalletTransaction::TYPE_DEBIT)) {
                $debit = $wallet->debit($data['amount'], "Penarikan #{$withdrawal->id}", $withdrawal);
                $debit->created_at = $requestedAt;
                $debit->save();
            }

            if ($data['status'] === Withdrawal::STATUS_REJECTED
                && ! $this->ledgerEntryExists($wallet, $withdrawal, WalletTransaction::TYPE_CREDIT)) {
                $refund = $wallet->credit($data['amount'], "Pengembalian penarikan #{$withdrawal->id}", $withdrawal);
                $refund->created_at = $handledAt;
                $refund->save();
            }
        }
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function requests(): array
    {
        return [
            [
                'store' => 'warung-bu-siti',
                'amount' => 50000,
                'status' => Withdrawal::STATUS_PENDING,
                'bank_name' => 'BCA',
                'account_number' => '8765432109',
                'account_name' => 'Siti Aminah',
                'store_note' => 'Tolong ditransfer hari ini ya, buat kulakan besok pagi.',
                'admin_note' => null,
                'days_ago' => 0,
            ],
            [
                'store' => 'warung-bu-siti',
                'amount' => 25000,
                'status' => Withdrawal::STATUS_PAID,
                'bank_name' => 'BCA',
                'account_number' => '8765432109',
                'account_name' => 'Siti Aminah',
                'store_note' => null,
                'admin_note' => 'Sudah ditransfer via BCA, silakan cek mutasi.',
                'days_ago' => 4,
            ],
            [
                'store' => 'warung-bang-jago',
                'amount' => 20000,
                'status' => Withdrawal::STATUS_REJECTED,
                'bank_name' => 'GoPay',
                'account_number' => '081234567890',
                'account_name' => 'Budi Santoso',
                'store_note' => 'Kirim ke GoPay saja biar cepat.',
                'admin_note' => 'Nama pemilik rekening belum sesuai dengan data warung. Mohon perbaiki dulu.',
                'days_ago' => 6,
            ],
        ];
    }
}
