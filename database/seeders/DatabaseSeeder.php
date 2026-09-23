<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seeds a full demo marketplace: accounts, warungs, catalogue, buyer and
     * seller orders across every workflow state, wallets, ledger and payouts.
     *
     * Every seeder is idempotent, so `db:seed` can be re-run safely.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            StoreSeeder::class,
            ProductSeeder::class,
            FavoriteSeeder::class,
            ContactMessageSeeder::class,
            SettingSeeder::class,
            ArticleSeeder::class,
            PaymentMethodSeeder::class,
            OrderSeeder::class,
            PaymentSeeder::class,
            WalletSeeder::class,
            WithdrawalSeeder::class,
        ]);
    }
}
