<?php

namespace Database\Seeders;

use App\Models\ContactMessage;
use Illuminate\Database\Seeder;

class ContactMessageSeeder extends Seeder
{
    public function run(): void
    {
        $messages = [
            [
                'name' => 'Dewi Lestari',
                'email' => 'dewi@example.com',
                'subject' => 'Pembayaran',
                'message' => 'Saya sudah transfer untuk pesanan WH-00004 tapi statusnya masih menunggu verifikasi sejak pagi. Mohon dibantu cek ya.',
            ],
            [
                'name' => 'Hendra Wijaya',
                'email' => 'hendra@example.com',
                'subject' => 'Mitra',
                'message' => 'Saya punya warung di daerah Tebet dan ingin bergabung jadi mitra. Apa saja syarat dan berapa komisinya?',
            ],
            [
                'name' => 'Sari Melati',
                'email' => 'sari@example.com',
                'subject' => 'Bantuan Teknis',
                'message' => 'Foto produk saya selalu gagal diunggah, ukurannya 1,5MB format JPG. Apakah ada batasan lain selain ukuran?',
            ],
            [
                'name' => 'Yusuf Ramadhan',
                'email' => 'yusuf@example.com',
                'subject' => 'Pesanan',
                'message' => 'Pesanan saya dibatalkan padahal stok masih ada. Apakah bisa saya pesan ulang dengan jumlah yang sama?',
            ],
        ];

        foreach ($messages as $message) {
            ContactMessage::firstOrCreate(
                ['email' => $message['email'], 'subject' => $message['subject']],
                $message,
            );
        }
    }
}
