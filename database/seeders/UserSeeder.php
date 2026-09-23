<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            [
                'name' => 'Admin Warung Hebat',
                'email' => 'admin@warunghebat.id',
                'password' => 'admin12345',
                'role' => 'admin',
            ],
            [
                'name' => 'Budi Santoso',
                'email' => 'budi@warunghebat.id',
                'password' => 'password123',
                'role' => 'penjual',
            ],
            [
                'name' => 'Siti Aminah',
                'email' => 'siti@warunghebat.id',
                'password' => 'password123',
                'role' => 'penjual',
            ],
            [
                'name' => 'Rina Wulandari',
                'email' => 'rina@warunghebat.id',
                'password' => 'password123',
                'role' => 'penjual',
            ],
            [
                'name' => 'Yu Ning',
                'email' => 'ning@warunghebat.id',
                'password' => 'password123',
                'role' => 'penjual',
            ],
            [
                'name' => 'Dewi Lestari',
                'email' => 'dewi@example.com',
                'password' => 'password123',
                'role' => 'pembeli',
                'points' => 240,
            ],
            [
                'name' => 'Agus Prasetyo',
                'email' => 'agus@example.com',
                'password' => 'password123',
                'role' => 'pembeli',
                'points' => 120,
            ],
            [
                'name' => 'Putri Handayani',
                'email' => 'putri@example.com',
                'password' => 'password123',
                'role' => 'pembeli',
                'points' => 80,
            ],
            [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => 'password123',
                'role' => 'pembeli',
                'points' => 40,
            ],
        ];

        foreach ($accounts as $account) {
            User::firstOrCreate(['email' => $account['email']], $account);
        }
    }
}
