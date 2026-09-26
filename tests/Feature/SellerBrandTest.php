<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\SellerVerification;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class SellerBrandTest extends TestCase
{
    use RefreshDatabase;

    public function test_seller_can_crud_own_brands(): void
    {
        $seller = $this->verifiedSeller();

        $this->actingAs($seller)->post(route('seller.brands.store'), ['name' => 'Indomie'])
            ->assertRedirect(route('seller.brands.index'));

        $brand = Brand::where('user_id', $seller->id)->where('name', 'Indomie')->sole();

        $this->actingAs($seller)->get(route('seller.brands.index'))->assertOk()->assertSee('Indomie');

        $this->actingAs($seller)->put(route('seller.brands.update', $brand), ['name' => 'Indomie Goreng'])
            ->assertRedirect(route('seller.brands.index'));

        $this->assertSame('Indomie Goreng', $brand->refresh()->name);

        $this->actingAs($seller)->delete(route('seller.brands.destroy', $brand))->assertRedirect();
        $this->assertDatabaseMissing('brands', ['id' => $brand->id]);
    }

    public function test_seller_cannot_manage_another_sellers_brand(): void
    {
        $seller = $this->verifiedSeller();
        $other = $this->verifiedSeller();
        $brand = Brand::factory()->for($other)->create(['name' => 'Sasa']);

        $this->actingAs($seller)->get(route('seller.brands.edit', $brand))->assertForbidden();
        $this->actingAs($seller)->put(route('seller.brands.update', $brand), ['name' => 'Diambil'])
            ->assertForbidden();
        $this->actingAs($seller)->delete(route('seller.brands.destroy', $brand))->assertForbidden();
    }

    public function test_seller_cannot_delete_brand_used_by_products(): void
    {
        $seller = $this->verifiedSeller();
        $brand = Brand::factory()->for($seller)->create(['name' => 'Sasa']);
        Product::factory()->for($seller)->create(['brand_id' => $brand->id]);

        $this->actingAs($seller)->delete(route('seller.brands.destroy', $brand))->assertRedirect();
        $this->assertDatabaseHas('brands', ['id' => $brand->id]);
    }

    public function test_product_form_has_promo_and_brand_radio_choices(): void
    {
        $seller = $this->verifiedSeller();

        $this->actingAs($seller)
            ->get(route('seller.products.create'))
            ->assertOk()
            ->assertSee('name="has_promo" value="yes"', false)
            ->assertSee('name="has_promo" value="no"', false)
            ->assertSee('id="promo-fields"', false)
            ->assertSee('name="has_brand" value="yes"', false)
            ->assertSee('name="has_brand" value="no"', false)
            ->assertSee('id="brand-fields"', false);
    }

    public function test_seller_can_create_product_with_existing_brand(): void
    {
        Storage::fake('public');
        $seller = $this->verifiedSeller();
        $brand = Brand::factory()->for($seller)->create(['name' => 'Indomie']);
        $category = Category::where('slug', 'makanan')->firstOrFail();

        $this->actingAs($seller)->post(route('seller.products.store'), [
            'name' => 'Indomie Goreng Spesial',
            'price' => '5000',
            'stock' => '20',
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'image' => UploadedFile::fake()->image('produk.jpg'),
        ])->assertRedirect(route('seller.products.index'));

        $this->assertSame($brand->id, Product::sole()->brand_id);
    }

    public function test_typing_new_brand_on_product_form_creates_master_brand(): void
    {
        Storage::fake('public');
        $seller = $this->verifiedSeller();
        $category = Category::where('slug', 'sembako')->firstOrFail();

        $this->actingAs($seller)->post(route('seller.products.store'), [
            'name' => 'Kecap Baru',
            'price' => '8000',
            'stock' => '10',
            'category_id' => $category->id,
            'new_brand' => 'Bango',
            'image' => UploadedFile::fake()->image('produk.jpg'),
        ])->assertRedirect(route('seller.products.index'));

        $brand = Brand::where('user_id', $seller->id)->where('name', 'Bango')->sole();

        $this->assertSame($brand->id, Product::sole()->brand_id);
    }

    public function test_seller_can_remove_brand_from_product_when_editing(): void
    {
        Storage::fake('public');
        $seller = $this->verifiedSeller();
        $brand = Brand::factory()->for($seller)->create(['name' => 'Indomie']);
        $product = Product::factory()->for($seller)->create(['brand_id' => $brand->id]);

        $this->actingAs($seller)->put(route('seller.products.update', $product), [
            'name' => $product->name,
            'price' => '5000',
            'stock' => '10',
            'category_id' => Category::where('slug', 'makanan')->value('id'),
            'has_brand' => 'no',
            'brand_id' => $brand->id,
            'new_brand' => 'Stale value',
            'image' => UploadedFile::fake()->image('produk.jpg'),
        ])->assertRedirect(route('seller.products.index'));

        $this->assertNull($product->fresh()->brand_id);
        $this->assertDatabaseHas('brands', ['id' => $brand->id]);
    }

    public function test_category_page_can_filter_products_by_brand(): void
    {
        $store = $this->storeFor('Warung Bang Jago');
        $category = Category::where('slug', 'makanan')->firstOrFail();
        $brand = Brand::factory()->for($store->user)->create(['name' => 'Indomie', 'slug' => 'indomie']);

        Product::factory()->for($store->user)->create([
            'name' => 'Indomie Goreng Tek-Tek',
            'category' => $category->name,
            'category_id' => $category->id,
            'brand_id' => $brand->id,
        ]);
        Product::factory()->for($store->user)->create([
            'name' => 'Nasi Uduk Biasa',
            'category' => $category->name,
            'category_id' => $category->id,
            'brand_id' => null,
        ]);

        $this->get('/kategori/makanan?brand=indomie')
            ->assertOk()
            ->assertSee('Indomie Goreng Tek-Tek')
            ->assertDontSee('Nasi Uduk Biasa');
    }

    public function test_search_page_can_filter_products_by_brand(): void
    {
        $store = $this->storeFor('Warung Bang Jago');
        $category = Category::where('slug', 'makanan')->firstOrFail();
        $brand = Brand::factory()->for($store->user)->create(['name' => 'Sasa', 'slug' => 'sasa']);

        Product::factory()->for($store->user)->create([
            'name' => 'Tepung Sasa Serbaguna',
            'category' => $category->name,
            'category_id' => $category->id,
            'brand_id' => $brand->id,
        ]);
        Product::factory()->for($store->user)->create([
            'name' => 'Tepung Biasa Curah',
            'category' => $category->name,
            'category_id' => $category->id,
            'brand_id' => null,
        ]);

        $this->get('/cari?q=Tepung&brand=sasa')
            ->assertOk()
            ->assertSee('Tepung Sasa Serbaguna')
            ->assertDontSee('Tepung Biasa Curah');
    }

    private function verifiedSeller(): User
    {
        $seller = User::factory()->create(['role' => 'penjual']);
        SellerVerification::factory()->for($seller)->verified()->create();
        Store::factory()->for($seller)->create();

        return $seller;
    }

    private function storeFor(string $name): Store
    {
        $seller = User::factory()->create(['role' => 'penjual']);
        SellerVerification::factory()->for($seller)->verified()->create();

        return Store::factory()->for($seller)->create([
            'name' => $name,
            'slug' => Str::slug($name),
        ]);
    }
}
