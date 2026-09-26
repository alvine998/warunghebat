<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\SellerVerification;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminCategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_category_with_auto_slug(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post(route('admin.categories.store'), [
            'name' => 'Makanan Beku',
            'icon' => 'snowflake',
            'description' => 'Nugget & dimsum',
            'sort_order' => 6,
            'is_active' => true,
        ])->assertRedirect(route('admin.categories'));

        $category = Category::where('name', 'Makanan Beku')->sole();

        $this->assertSame('makanan-beku', $category->slug);
        $this->assertTrue($category->is_active);
    }

    public function test_admin_can_rename_toggle_and_delete_unused_category(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = Category::factory()->create(['name' => 'Kategori Lama', 'is_active' => true]);

        $this->actingAs($admin)->put(route('admin.categories.update', $category), [
            'name' => 'Kategori Baru',
            'is_active' => true,
        ])->assertRedirect(route('admin.categories'));

        $category->refresh();

        $this->assertSame('Kategori Baru', $category->name);

        $this->actingAs($admin)->patch(route('admin.categories.toggle', $category))->assertRedirect();
        $this->assertFalse($category->refresh()->is_active);

        $this->get('/kategori/'.$category->refresh()->slug)->assertNotFound();

        $this->actingAs($admin)->delete(route('admin.categories.destroy', $category))->assertRedirect();
        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    public function test_admin_cannot_delete_category_used_by_products(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $seller = $this->verifiedSeller();
        $product = Product::factory()->for($seller)->create();
        $category = $product->categoryRef;

        $this->actingAs($admin)
            ->delete(route('admin.categories.destroy', $category))
            ->assertRedirect();

        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }

    public function test_guests_and_sellers_cannot_open_category_backoffice(): void
    {
        $this->get(route('admin.categories'))->assertRedirect(route('login'));

        $seller = $this->verifiedSeller();

        $this->actingAs($seller)->get(route('admin.categories'))->assertRedirect(route('dashboard'));
    }

    public function test_new_category_appears_on_home_and_serves_products(): void
    {
        $store = $this->storeFor('Warung Bang Jago');
        $category = Category::factory()->create(['name' => 'Kopi Kekinian', 'is_active' => true]);
        Product::factory()->for($store->user)->create([
            'name' => 'Es Kopi Susu Tetangga',
            'category' => $category->name,
            'category_id' => $category->id,
        ]);

        $this->get('/')->assertOk()->assertSee('Kopi Kekinian');
        $this->get('/kategori/'.$category->slug)->assertOk()->assertSee('Es Kopi Susu Tetangga');
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
