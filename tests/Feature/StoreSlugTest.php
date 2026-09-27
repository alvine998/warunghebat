<?php

namespace Tests\Feature;

use App\Models\SellerVerification;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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

    public function test_store_photo_under_one_megabyte_is_accepted(): void
    {
        Storage::fake('public');
        $seller = User::factory()->create(['role' => 'penjual']);
        SellerVerification::factory()->for($seller)->verified()->create();
        $store = Store::resolveFor($seller);

        $this->actingAs($seller)->put(route('seller.store.update'), [
            'name' => $store->name,
            'latitude' => -6.2297,
            'longitude' => 106.8294,
            'image' => UploadedFile::fake()->image('warung.jpg')->size(950),
        ])->assertSessionHasNoErrors();

        $this->assertNotNull($store->fresh()->image_path);
    }

    public function test_store_photo_over_950_kb_is_rejected(): void
    {
        Storage::fake('public');
        $seller = User::factory()->create(['role' => 'penjual']);
        SellerVerification::factory()->for($seller)->verified()->create();
        $store = Store::resolveFor($seller);

        $this->actingAs($seller)->put(route('seller.store.update'), [
            'name' => $store->name,
            'latitude' => -6.2297,
            'longitude' => 106.8294,
            'image' => UploadedFile::fake()->image('warung.jpg')->size(951),
        ])->assertSessionHasErrors(['image']);

        $this->assertNull($store->fresh()->image_path);
    }

    public function test_store_photo_form_uses_shared_image_compression(): void
    {
        $seller = User::factory()->create(['role' => 'penjual']);
        SellerVerification::factory()->for($seller)->verified()->create();
        Store::resolveFor($seller);

        $this->actingAs($seller)
            ->get(route('seller.store.edit'))
            ->assertOk()
            ->assertSee('data-compress-images', false)
            ->assertSee('otomatis dikompres di bawah 1MB');
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
