<?php

namespace Tests\Feature;

use App\Models\SellerVerification;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreSlugTest extends TestCase
{
    use RefreshDatabase;

    public function test_update_derives_slug_from_name(): void
    {
        $seller = User::factory()->create(['role' => 'penjual']);
        SellerVerification::factory()->for($seller)->verified()->create();
        Store::resolveFor($seller);

        $this->actingAs($seller)->put(route('seller.store.update'), [
            'name' => 'Warung Bang Jago',
            'latitude' => -6.2297,
            'longitude' => 106.8294,
        ])->assertSessionHasNoErrors();

        $this->assertSame('warung-bang-jago', $seller->store()->first()->slug);
    }

    public function test_duplicate_names_get_suffixed_slug(): void
    {
        $a = User::factory()->create(['role' => 'penjual']);
        $b = User::factory()->create(['role' => 'penjual']);
        SellerVerification::factory()->for($a)->verified()->create();
        SellerVerification::factory()->for($b)->verified()->create();
        Store::resolveFor($a);
        Store::resolveFor($b);

        $this->actingAs($a)->put(route('seller.store.update'), ['name' => 'Warung Sama', 'latitude' => -6.2297, 'longitude' => 106.8294]);
        $this->actingAs($b)->put(route('seller.store.update'), ['name' => 'Warung Sama', 'latitude' => -6.2352, 'longitude' => 106.8498]);

        $slugs = Store::whereIn('user_id', [$a->id, $b->id])->orderBy('id')->pluck('slug')->all();

        $this->assertSame(['warung-sama', 'warung-sama-2'], $slugs);
    }
}
