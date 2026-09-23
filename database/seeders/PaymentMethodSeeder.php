<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use Database\Seeders\Support\PlaceholderImage;
use Illuminate\Database\Seeder;

class PaymentMethodSeeder extends Seeder
{
    public function run(): void
    {
        $methods = [
            [
                'name' => 'BCA a/n Warung Hebat',
                'type' => PaymentMethod::TYPE_BANK,
                'account_number' => '1234567890',
                'account_name' => 'PT Warung Hebat Indonesia',
                'instructions' => 'Transfer sesuai nominal pesanan, lalu unggah bukti transfer di halaman pesanan.',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'QRIS Warung Hebat',
                'type' => PaymentMethod::TYPE_QRIS,
                'account_number' => null,
                'account_name' => 'Warung Hebat',
                'instructions' => 'Scan kode QRIS dengan aplikasi bank atau e-wallet apa pun, lalu unggah tangkapan layarnya.',
                'is_active' => true,
                'sort_order' => 2,
                'image' => '#1A130D',
            ],
            [
                'name' => 'GoPay a/n Warung Hebat',
                'type' => PaymentMethod::TYPE_EWALLET,
                'account_number' => '081234567890',
                'account_name' => 'Warung Hebat',
                'instructions' => 'Kirim saldo GoPay ke nomor ini, lalu unggah bukti transfernya.',
                'is_active' => false,
                'sort_order' => 3,
            ],
        ];

        foreach ($methods as $data) {
            $method = PaymentMethod::firstOrCreate(['name' => $data['name']], [
                'type' => $data['type'],
                'account_number' => $data['account_number'],
                'account_name' => $data['account_name'],
                'instructions' => $data['instructions'],
                'is_active' => $data['is_active'],
                'sort_order' => $data['sort_order'],
            ]);

            if (isset($data['image']) && ! $method->image_path) {
                $method->update([
                    'image_path' => PlaceholderImage::put('payment-methods/qris-warung-hebat.png', $data['image'], 500, 500),
                ]);
            }
        }
    }
}
