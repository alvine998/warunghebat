<?php

namespace Tests\Feature;

use App\Models\ContactMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminInquiriesTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_admin_cannot_open_the_inquiries_page(): void
    {
        $buyer = User::factory()->create(['role' => 'pembeli']);

        $this->actingAs($buyer)
            ->get(route('admin.inquiries'))
            ->assertRedirect(route('dashboard'));
    }

    public function test_admin_sees_inquiries_grouped_by_subject(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        ContactMessage::create([
            'name' => 'Dewi Lestari',
            'email' => 'dewi@example.com',
            'subject' => 'Pembayaran',
            'message' => 'Saya sudah transfer tapi status masih menunggu verifikasi.',
        ]);
        ContactMessage::create([
            'name' => 'Hendra Wijaya',
            'email' => 'hendra@example.com',
            'subject' => 'Mitra',
            'message' => 'Saya ingin bergabung menjadi mitra warung di Tebet.',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.inquiries'))
            ->assertOk()
            ->assertSee('Dewi Lestari')
            ->assertSee('Hendra Wijaya');

        $this->actingAs($admin)
            ->get(route('admin.inquiries', ['subject' => 'Pembayaran']))
            ->assertOk()
            ->assertSee('Dewi Lestari')
            ->assertDontSee('Hendra Wijaya');
    }

    public function test_inquiries_can_be_searched_by_name_email_and_message(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        ContactMessage::create([
            'name' => 'Sari Melati',
            'email' => 'sari@example.com',
            'subject' => 'Bantuan Teknis',
            'message' => 'Foto produk saya selalu gagal diunggah.',
        ]);
        ContactMessage::create([
            'name' => 'Orang Lain',
            'email' => 'lain@example.com',
            'subject' => 'Pesanan',
            'message' => 'Kapan pesanan saya sampai?',
        ]);

        foreach (['Sari Melati', 'sari@example.com', 'gagal diunggah'] as $term) {
            $this->actingAs($admin)
                ->get(route('admin.inquiries', ['search' => $term]))
                ->assertOk()
                ->assertSee('Sari Melati')
                ->assertDontSee('Orang Lain');
        }
    }

    public function test_inquiries_are_paginated(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        foreach (range(1, 16) as $i) {
            ContactMessage::create([
                'name' => 'Pengirim '.str_pad((string) $i, 2, '0', STR_PAD_LEFT),
                'email' => "pengirim{$i}@example.com",
                'subject' => 'Lainnya',
                'message' => "Pesan nomor {$i} yang cukup panjang.",
            ]);
        }

        $this->actingAs($admin)
            ->get(route('admin.inquiries'))
            ->assertOk()
            ->assertSee('Menampilkan')
            ->assertDontSee('pengirim1@example.com');

        $this->actingAs($admin)
            ->get(route('admin.inquiries', ['page' => 2]))
            ->assertOk()
            ->assertSee('pengirim1@example.com');
    }
}
