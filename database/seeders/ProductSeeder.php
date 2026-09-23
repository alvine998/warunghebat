<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Store;
use Database\Seeders\Support\PlaceholderImage;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->catalogue() as $slug => $products) {
            $store = Store::where('slug', $slug)->first();

            if (! $store) {
                continue;
            }

            foreach ($products as $data) {
                $product = Product::firstOrCreate(
                    ['user_id' => $store->user_id, 'name' => $data['name']],
                    [
                        'description' => $data['description'],
                        'price' => $data['price'],
                        'stock' => $data['stock'],
                        'category' => $data['category'],
                        'status' => $data['status'],
                        'rejection_reason' => $data['rejection_reason'] ?? null,
                    ],
                );

                if (! $product->image_path) {
                    $product->update([
                        'image_path' => PlaceholderImage::put(
                            "products/{$product->id}-{$slug}.png",
                            $data['color'],
                            640,
                            480,
                        ),
                    ]);
                }
            }
        }
    }

    /**
     * Catalogue per store slug. Statuses cover the admin verification queue:
     * one product still pending and one rejected, plus low and empty stock.
     *
     * @return array<string, list<array<string, mixed>>>
     */
    private function catalogue(): array
    {
        return [
            'warung-bang-jago' => [
                ['name' => 'Nasi Goreng Tek-Tek', 'description' => 'Nasi goreng kampung dengan telur mata sapi dan kerupuk.', 'price' => 15000, 'stock' => 20, 'category' => 'Makanan', 'status' => 'approved', 'color' => '#FF7A29'],
                ['name' => 'Mie Ayam Bangka', 'description' => 'Mie kuning kenyal, topping ayam kecap dan pangsit goreng.', 'price' => 12000, 'stock' => 15, 'category' => 'Makanan', 'status' => 'approved', 'color' => '#F95D0B'],
                ['name' => 'Gorengan Campur', 'description' => 'Tahu, tempe, bakwan, dan cireng goreng panas.', 'price' => 5000, 'stock' => 4, 'category' => 'Jajanan', 'status' => 'approved', 'color' => '#EA4A05'],
                ['name' => 'Es Teh Manis', 'description' => 'Teh tubruk manis dengan es batu serut.', 'price' => 4000, 'stock' => 60, 'category' => 'Minuman', 'status' => 'approved', 'color' => '#159A4C'],
                ['name' => 'Kerupuk Udang', 'description' => 'Kerupuk udang goreng, renyah dan gurih.', 'price' => 5000, 'stock' => 0, 'category' => 'Harian', 'status' => 'approved', 'color' => '#C23807'],
                ['name' => 'Ayam Geprek Sambal Ijo', 'description' => 'Ayam crispy digeprek sambal ijo, level pedas bisa dipilih.', 'price' => 20000, 'stock' => 10, 'category' => 'Makanan', 'status' => 'pending', 'color' => '#7A6C5E'],
            ],
            'warung-bu-siti' => [
                ['name' => 'Beras Premium 5kg', 'description' => 'Beras pulen premium kemasan 5 kilogram.', 'price' => 65000, 'stock' => 12, 'category' => 'Sembako', 'status' => 'approved', 'color' => '#159A4C'],
                ['name' => 'Minyak Goreng 2L', 'description' => 'Minyak goreng sawit kemasan pouch 2 liter.', 'price' => 35000, 'stock' => 8, 'category' => 'Sembako', 'status' => 'approved', 'color' => '#0D7A3B'],
                ['name' => 'Gula Pasir 1kg', 'description' => 'Gula pasir putih kemasan 1 kilogram.', 'price' => 15000, 'stock' => 25, 'category' => 'Sembako', 'status' => 'approved', 'color' => '#FFE8BD'],
                ['name' => 'Telur Ayam 1kg', 'description' => 'Telur ayam negeri segar, dikemas per kilogram.', 'price' => 28000, 'stock' => 4, 'category' => 'Sembako', 'status' => 'approved', 'color' => '#FFC999'],
                ['name' => 'Sabun Cuci Piring', 'description' => 'Sabun cuci piring refill 800ml, wangi jeruk nipis.', 'price' => 12000, 'stock' => 0, 'category' => 'Harian', 'status' => 'approved', 'color' => '#7A6C5E'],
            ],
            'kedai-kopi-hebat' => [
                ['name' => 'Kopi Susu Gula Aren', 'description' => 'Espresso, susu segar, dan gula aren cair. Best seller.', 'price' => 18000, 'stock' => 30, 'category' => 'Minuman', 'status' => 'approved', 'color' => '#C23807'],
                ['name' => 'Es Kopi Tubruk', 'description' => 'Kopi tubruk robusta disajikan dingin dengan es batu.', 'price' => 10000, 'stock' => 20, 'category' => 'Minuman', 'status' => 'approved', 'color' => '#41352A'],
                ['name' => 'Matcha Latte', 'description' => 'Matcha Jepang dengan susu oat, manisnya bisa diatur.', 'price' => 22000, 'stock' => 2, 'category' => 'Minuman', 'status' => 'approved', 'color' => '#159A4C'],
                ['name' => 'Teh Tarik', 'description' => 'Teh susu tarik khas kedai, disajikan hangat atau dingin.', 'price' => 15000, 'stock' => 18, 'category' => 'Minuman', 'status' => 'approved', 'color' => '#FFA566'],
                ['name' => 'Croissant Butter', 'description' => 'Croissant mentega panggang, cocok menemani kopi.', 'price' => 19000, 'stock' => 6, 'category' => 'Jajanan', 'status' => 'rejected', 'rejection_reason' => 'Foto produk kurang jelas, mohon unggah ulang dengan pencahayaan lebih baik.', 'color' => '#FFE6CC'],
            ],
            'jajan-pasar-yu-ning' => [
                ['name' => 'Kue Lapis', 'description' => 'Kue lapis legit tradisional, dipotong per kotak.', 'price' => 25000, 'stock' => 10, 'category' => 'Jajanan', 'status' => 'approved', 'color' => '#FF7A29'],
                ['name' => 'Risoles Mayo', 'description' => 'Risoles isi smoked beef, telur, dan saus mayo.', 'price' => 20000, 'stock' => 14, 'category' => 'Jajanan', 'status' => 'approved', 'color' => '#FFE8BD'],
                ['name' => 'Dadar Gulung', 'description' => 'Dadar gulung isi kelapa gula merah, manis legit.', 'price' => 18000, 'stock' => 3, 'category' => 'Jajanan', 'status' => 'approved', 'color' => '#159A4C'],
                ['name' => 'Nagasari', 'description' => 'Nagasari pisang dalam balutan tepung beras.', 'price' => 15000, 'stock' => 16, 'category' => 'Jajanan', 'status' => 'approved', 'color' => '#FFF3DC'],
                ['name' => 'Kue Putu', 'description' => 'Kue putu bambu dengan gula merah dan kelapa parut.', 'price' => 12000, 'stock' => 0, 'category' => 'Jajanan', 'status' => 'approved', 'color' => '#0D7A3B'],
            ],
        ];
    }
}
