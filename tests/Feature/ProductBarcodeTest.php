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

class ProductBarcodeTest extends TestCase
{
    use RefreshDatabase;

    public function test_seller_can_save_product_with_barcode(): void
    {
        Storage::fake('public');
        $seller = $this->verifiedSeller();

        $this->actingAs($seller)->post(route('seller.products.store'), [
            'name' => 'Indomie Goreng',
            'price' => '3500',
            'stock' => '20',
            'category' => 'Sembako',
            'barcode' => '8991234567890',
            'image' => UploadedFile::fake()->image('produk.jpg'),
        ])->assertRedirect(route('seller.products.index'));

        $this->assertSame('8991234567890', Product::sole()->barcode);
    }

    public function test_empty_barcode_is_stored_as_null(): void
    {
        Storage::fake('public');
        $seller = $this->verifiedSeller();

        $this->actingAs($seller)->post(route('seller.products.store'), [
            'name' => 'Teh Manis',
            'price' => '5000',
            'stock' => '20',
            'category' => 'Minuman',
            'barcode' => '',
            'image' => UploadedFile::fake()->image('produk.jpg'),
        ])->assertRedirect(route('seller.products.index'));

        $this->assertNull(Product::sole()->barcode);
    }

    public function test_barcode_must_be_unique_per_seller_but_reusable_across_warungs(): void
    {
        Storage::fake('public');
        $seller = $this->verifiedSeller();
        Product::factory()->for($seller)->create(['barcode' => '8991234567890', 'status' => 'approved']);

        $payload = [
            'name' => 'Indomie Rebus',
            'price' => '3500',
            'stock' => '20',
            'category' => 'Sembako',
            'barcode' => '8991234567890',
            'image' => UploadedFile::fake()->image('produk.jpg'),
        ];

        $this->actingAs($seller)->post(route('seller.products.store'), $payload)
            ->assertSessionHasErrors(['barcode']);

        $this->assertSame(1, Product::count());

        $otherSeller = $this->verifiedSeller();
        $this->actingAs($otherSeller)->post(route('seller.products.store'), $payload)
            ->assertRedirect(route('seller.products.index'));

        $this->assertSame(2, Product::count());
    }

    public function test_transaction_create_page_exposes_barcodes_for_scanning(): void
    {
        $seller = User::factory()->create(['role' => 'penjual']);
        SellerVerification::factory()->for($seller)->verified()->create();
        Store::factory()->for($seller)->create();
        $product = Product::factory()->for($seller)->create([
            'barcode' => '8991234567890',
            'status' => 'approved',
            'stock' => 10,
        ]);

        $this->actingAs($seller)->get(route('seller.transactions.create'))
            ->assertOk()
            ->assertSee('id="barcode-scan-input"', false)
            ->assertSee('id="barcode-scan-btn"', false)
            ->assertSee('id="barcode-scanner-modal"', false)
            ->assertSee('data-barcode="'.$product->barcode.'"', false);
    }

    private function verifiedSeller(): User
    {
        $seller = User::factory()->create(['role' => 'penjual']);
        SellerVerification::factory()->for($seller)->verified()->create();
        Store::factory()->for($seller)->create();

        return $seller;
    }
}
