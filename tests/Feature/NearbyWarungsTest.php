<?php

namespace Tests\Feature;

use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NearbyWarungsTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_shows_stores_open_first(): void
    {
        $closed = User::factory()->create(['role' => 'penjual']);
        Store::factory()->for($closed)->create(['name' => 'Warung Tutup', 'is_open' => false]);
        $open = User::factory()->create(['role' => 'penjual']);
        Store::factory()->for($open)->create(['name' => 'Warung Buka', 'is_open' => true]);

        $this->get('/')->assertOk()->assertSeeInOrder(['Warung Buka', 'Warung Tutup']);
    }

    public function test_home_sorts_by_distance_when_coords_given(): void
    {
        $far = User::factory()->create(['role' => 'penjual']);
        Store::factory()->for($far)->create(['name' => 'Warung Jauh', 'latitude' => -6.20, 'longitude' => 106.85, 'is_open' => true]);
        $near = User::factory()->create(['role' => 'penjual']);
        Store::factory()->for($near)->create(['name' => 'Warung Dekat', 'latitude' => -6.2297, 'longitude' => 106.8294, 'is_open' => true]);

        $this->get('/?lat=-6.2297&lng=106.8294')->assertOk()->assertSeeInOrder(['Warung Dekat', 'Warung Jauh']);
    }

    public function test_home_filters_outside_radius(): void
    {
        $far = User::factory()->create(['role' => 'penjual']);
        Store::factory()->for($far)->create(['name' => 'Warung Luar', 'latitude' => -6.0, 'longitude' => 107.0, 'is_open' => true]);

        $this->get('/?lat=-6.2297&lng=106.8294&radius=1')->assertOk()->assertDontSee('Warung Luar');
    }

    public function test_home_empty_state(): void
    {
        $this->get('/')->assertOk()->assertSee('Belum ada warung');
    }

    public function test_home_links_to_nearby_list_page(): void
    {
        $owner = User::factory()->create(['role' => 'penjual']);
        Store::factory()->for($owner)->create(['name' => 'Warung Dekat', 'latitude' => -6.2297, 'longitude' => 106.8294]);

        $this->get('/?lat=-6.2297&lng=106.8294&radius=2')
            ->assertOk()
            ->assertSee(route('store.index', ['lat' => -6.2297, 'lng' => 106.8294, 'radius' => 2]));
    }

    public function test_nearby_page_shows_stores_open_first(): void
    {
        $closed = User::factory()->create(['role' => 'penjual']);
        Store::factory()->for($closed)->create(['name' => 'Warung Tutup', 'is_open' => false]);
        $open = User::factory()->create(['role' => 'penjual']);
        Store::factory()->for($open)->create(['name' => 'Warung Buka', 'is_open' => true]);

        $this->get('/warung')->assertOk()->assertSeeInOrder(['Warung Buka', 'Warung Tutup']);
    }

    public function test_nearby_page_sorts_by_distance_when_coords_given(): void
    {
        $far = User::factory()->create(['role' => 'penjual']);
        Store::factory()->for($far)->create(['name' => 'Warung Jauh', 'latitude' => -6.20, 'longitude' => 106.85, 'is_open' => true]);
        $near = User::factory()->create(['role' => 'penjual']);
        Store::factory()->for($near)->create(['name' => 'Warung Dekat', 'latitude' => -6.2297, 'longitude' => 106.8294, 'is_open' => true]);

        $this->get('/warung?lat=-6.2297&lng=106.8294')->assertOk()->assertSeeInOrder(['Warung Dekat', 'Warung Jauh']);
    }

    public function test_nearby_page_filters_outside_radius(): void
    {
        $far = User::factory()->create(['role' => 'penjual']);
        Store::factory()->for($far)->create(['name' => 'Warung Luar', 'latitude' => -6.0, 'longitude' => 107.0, 'is_open' => true]);

        $this->get('/warung?lat=-6.2297&lng=106.8294&radius=1')->assertOk()->assertDontSee('Warung Luar');
    }

    public function test_nearby_page_paginates_stores(): void
    {
        Store::factory()->count(13)->create(['is_open' => true])->each(
            fn (Store $store, int $index) => $store->update(['name' => 'Warung-'.str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT)])
        );

        $this->get('/warung')->assertOk()->assertSee('Warung-13')->assertDontSee('Warung-01');
        $this->get('/warung?page=2')->assertOk()->assertSee('Warung-01')->assertDontSee('Warung-13');
    }
}
