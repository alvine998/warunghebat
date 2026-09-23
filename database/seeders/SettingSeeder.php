<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        // Only fills the gaps, so values an admin already changed are kept.
        $defaults = [
            Setting::WITHDRAWAL_MIN => 10000,
            Setting::WITHDRAWAL_MAX => 5000000,
            // Seeded at 5% so the commission column and ledger math show real
            // numbers; set it to 0 in Pengaturan for the 0% launch promise.
            Setting::COMMISSION_PERCENT => 5,
        ];

        foreach ($defaults as $key => $value) {
            if (! Setting::where('key', $key)->exists()) {
                Setting::setValue($key, $value);
            }
        }
    }
}
