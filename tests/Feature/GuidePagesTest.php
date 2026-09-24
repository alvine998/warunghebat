<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuidePagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_guide_hub_links_both_audiences(): void
    {
        $this->get(route('guides.index'))
            ->assertOk()
            ->assertSee('Panduan Pembeli', false)
            ->assertSee('Panduan Penjual', false)
            ->assertSee(route('guides.buyer'), false)
            ->assertSee(route('guides.seller'), false)
            ->assertSee('<link rel="canonical" href="'.route('guides.index').'"', false)
            ->assertSee('name="robots" content="index, follow"', false);
    }

    public function test_buyer_guide_covers_the_checkout_flow(): void
    {
        $this->get(route('guides.buyer'))
            ->assertOk()
            ->assertSee('Cara belanja dari warung tetangga', false)
            ->assertSee('Checkout & bayar', false)
            ->assertSee('Menunggu verifikasi', false)
            ->assertSee(route('guides.seller'), false)
            ->assertSee('"@type":"HowTo"', false);
    }

    public function test_seller_guide_covers_the_merchant_flow(): void
    {
        $this->get(route('guides.seller'))
            ->assertOk()
            ->assertSee('Buka warung online dalam 5 menit', false)
            ->assertSee('Dompet & pencairan dana', false)
            ->assertSee('Menunggu', false)
            ->assertSee(route('guides.buyer'), false)
            ->assertSee('"@type":"HowTo"', false);
    }

    public function test_sitemap_lists_guide_pages(): void
    {
        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertSee(route('guides.index'), false)
            ->assertSee(route('guides.buyer'), false)
            ->assertSee(route('guides.seller'), false);
    }
}
