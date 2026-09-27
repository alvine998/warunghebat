<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\SellerVerification;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductPhotoTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_form_offers_camera_and_gallery_picker(): void
    {
        $seller = $this->verifiedSeller();

        $this->actingAs($seller)
            ->get(route('seller.products.create'))
            ->assertOk()
            ->assertSee('Ambil Foto', false)
            ->assertSee('Galeri', false)
            ->assertSee('otomatis dikompres di bawah 1MB', false)
            ->assertSee('id="photo-camera-input"', false)
            ->assertSee('capture="environment"', false)
            ->assertSee('id="photo-gallery-input"', false)
            ->assertSee('maximumImageBytes', false)
            ->assertSee('id="product-submit-btn"', false)
            ->assertSee('Masih mengompres foto', false);
    }

    public function test_product_store_accepts_image_at_or_below_one_megabyte(): void
    {
        Storage::fake('public');
        $seller = $this->verifiedSeller();

        $this->actingAs($seller)->post(route('seller.products.store'), [
            'name' => 'Indomie Goreng',
            'price' => '3500',
            'stock' => '20',
            'category' => 'Sembako',
            'image' => UploadedFile::fake()->image('produk.jpg')->size(950),
        ])->assertRedirect(route('seller.products.index'));

        $this->assertSame('Indomie Goreng', Product::sole()->name);
    }

    public function test_product_store_rejects_image_over_950_kb(): void
    {
        Storage::fake('public');
        $seller = $this->verifiedSeller();

        $this->actingAs($seller)->post(route('seller.products.store'), [
            'name' => 'Indomie Goreng',
            'price' => '3500',
            'stock' => '20',
            'category' => 'Sembako',
            'image' => UploadedFile::fake()->image('produk.jpg')->size(951),
        ])->assertSessionHasErrors(['image']);

        $this->assertSame(0, Product::count());
    }

    public function test_product_update_rejects_image_over_950_kb(): void
    {
        Storage::fake('public');
        $seller = $this->verifiedSeller();
        $product = Product::factory()->for($seller)->create(['status' => 'pending']);

        $this->actingAs($seller)->put(route('seller.products.update', $product), [
            'name' => $product->name,
            'price' => (string) $product->price,
            'stock' => (string) $product->stock,
            'category' => 'Sembako',
            'image' => UploadedFile::fake()->image('baru.jpg')->size(951),
        ])->assertSessionHasErrors(['image']);
    }

    private function verifiedSeller(): User
    {
        $seller = User::factory()->create(['role' => 'penjual']);
        SellerVerification::factory()->for($seller)->verified()->create();
        Store::factory()->for($seller)->create();

        return $seller;
    }
}
