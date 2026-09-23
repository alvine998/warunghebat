<?php

namespace Database\Seeders;

use App\Models\Store;
use App\Models\User;
use Database\Seeders\Support\PlaceholderImage;
use Illuminate\Database\Seeder;

class StoreSeeder extends Seeder
{
    public function run(): void
    {
        $stores = [
            [
                'owner' => 'budi@warunghebat.id',
                'name' => 'Warung Bang Jago',
                'slug' => 'warung-bang-jago',
                'description' => 'Gorengan hangat tiap sore dan nasi goreng tek-tek legendaris sejak 2014.',
                'address' => 'Jl. Tebet Raya No. 12, Jakarta Selatan',
                'latitude' => -6.2297,
                'longitude' => 106.8546,
                'phone' => '081234567890',
                'open_time' => '07:00',
                'close_time' => '21:00',
                'is_open' => true,
                'color' => '#FF7A29',
            ],
            [
                'owner' => 'siti@warunghebat.id',
                'name' => 'Warung Bu Siti',
                'slug' => 'warung-bu-siti',
                'description' => 'Sembako harian lengkap, harga tetangga, bisa pesan antar sekitar Tebet.',
                'address' => 'Jl. Tebet Barat Dalam No. 8, Jakarta Selatan',
                'latitude' => -6.2352,
                'longitude' => 106.8498,
                'phone' => '081298765432',
                'open_time' => '06:00',
                'close_time' => '20:00',
                'is_open' => true,
                'color' => '#159A4C',
            ],
            [
                'owner' => 'rina@warunghebat.id',
                'name' => 'Kedai Kopi Hebat',
                'slug' => 'kedai-kopi-hebat',
                'description' => 'Kopi susu gula aren, manual brew, dan camilan sore. Tempat nongkrong kecil di Tebet.',
                'address' => 'Jl. Dr. Saharjo No. 45, Jakarta Selatan',
                'latitude' => -6.2215,
                'longitude' => 106.8583,
                'phone' => '081377665544',
                'open_time' => '08:00',
                'close_time' => '22:00',
                'is_open' => true,
                'color' => '#C23807',
            ],
            [
                'owner' => 'ning@warunghebat.id',
                'name' => 'Jajan Pasar Yu Ning',
                'slug' => 'jajan-pasar-yu-ning',
                'description' => 'Kue basah pasar tradisional, dibuat setiap subuh. Tutup sementara sampai dapur selesai renovasi.',
                'address' => 'Jl. Tebet Timur Dalam No. 21, Jakarta Selatan',
                'latitude' => -6.2265,
                'longitude' => 106.8620,
                'phone' => '081355443322',
                'open_time' => '05:00',
                'close_time' => '12:00',
                'is_open' => false,
                'color' => '#7A6C5E',
            ],
        ];

        foreach ($stores as $data) {
            $owner = User::where('email', $data['owner'])->first();

            if (! $owner) {
                continue;
            }

            $store = Store::firstOrCreate(['slug' => $data['slug']], [
                'user_id' => $owner->id,
                'name' => $data['name'],
                'description' => $data['description'],
                'address' => $data['address'],
                'latitude' => $data['latitude'],
                'longitude' => $data['longitude'],
                'phone' => $data['phone'],
                'open_time' => $data['open_time'],
                'close_time' => $data['close_time'],
                'is_open' => $data['is_open'],
            ]);

            if (! $store->image_path) {
                $store->update([
                    'image_path' => PlaceholderImage::put("stores/{$data['slug']}.png", $data['color'], 900, 560),
                ]);
            }
        }
    }
}
