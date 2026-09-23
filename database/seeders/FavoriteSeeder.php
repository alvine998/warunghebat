<?php

namespace Database\Seeders;

use App\Models\Favorite;
use App\Models\User;
use Illuminate\Database\Seeder;

class FavoriteSeeder extends Seeder
{
    public function run(): void
    {
        $favorites = [
            ['buyer' => 'dewi@example.com', 'warung' => 'Warung Bang Jago'],
            ['buyer' => 'dewi@example.com', 'warung' => 'Kedai Kopi Hebat'],
            ['buyer' => 'agus@example.com', 'warung' => 'Warung Bu Siti'],
            ['buyer' => 'putri@example.com', 'warung' => 'Jajan Pasar Yu Ning'],
            ['buyer' => 'test@example.com', 'warung' => 'Warung Bang Jago'],
        ];

        foreach ($favorites as $favorite) {
            $buyer = User::where('email', $favorite['buyer'])->first();

            if (! $buyer) {
                continue;
            }

            Favorite::firstOrCreate([
                'user_id' => $buyer->id,
                'warung_name' => $favorite['warung'],
            ]);
        }
    }
}
