<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\StoreRating;
use Illuminate\Database\Seeder;

class StoreRatingSeeder extends Seeder
{
    /** @var list<string> */
    private const COMMENTS = [
        'Makanannya sampai masih hangat, mantap!',
        'Pelayanan ramah, bakal pesan lagi.',
        'Sesuai foto dan harga warung asli.',
        'Antar cepat, warungnya recommended.',
        'Rasanya enak, porsinya juga pas.',
        'Barang lengkap, penjual sopan.',
        'Kopi susunya juara, tempat nyaman.',
        'Beres semua, tinggal tunggu pesanan.',
    ];

    public function run(): void
    {
        // 1 completed order = 1 rating, so re-running would violate order_id unique.
        if (StoreRating::exists()) {
            $this->command?->info('Store ratings already seeded, skipping.');

            return;
        }

        $orders = Order::query()
            ->where('status', Order::STATUS_COMPLETED)
            ->whereNotNull('store_id')
            ->with('store:id,slug')
            ->get();

        foreach ($orders as $index => $order) {
            // Skip a slice so at least one store stays at "Belum ada rating".
            if ($order->store?->slug === 'kedai-kopi-hebat') {
                continue;
            }

            $order->rating()->create([
                'user_id' => $order->user_id,
                'store_id' => $order->store_id,
                'rating' => [5, 5, 4, 5, 4, 3, 5, 4][$index % 8],
                'comment' => self::COMMENTS[$index % count(self::COMMENTS)],
            ]);
        }
    }
}
