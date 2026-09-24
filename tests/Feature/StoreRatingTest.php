<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Store;
use App\Models\StoreRating;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreRatingTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_when_rating(): void
    {
        $order = Order::factory()->completed()->create();

        $this->post(route('orders.rate', $order), ['rating' => 5])
            ->assertRedirect(route('login'));
    }

    public function test_another_buyer_cannot_rate_someone_elses_order(): void
    {
        $owner = User::factory()->create(['role' => 'pembeli']);
        $stranger = User::factory()->create(['role' => 'pembeli']);
        $order = Order::factory()->completed()->for($owner, 'user')->create();

        $this->actingAs($stranger)
            ->post(route('orders.rate', $order), ['rating' => 5])
            ->assertForbidden();

        $this->assertSame(0, StoreRating::count());
    }

    public function test_buyer_can_rate_a_completed_order_once(): void
    {
        $buyer = User::factory()->create(['role' => 'pembeli']);
        $order = Order::factory()->completed()->for($buyer, 'user')->create();

        $this->actingAs($buyer)
            ->post(route('orders.rate', $order), [
                'rating' => 5,
                'comment' => 'Mantap!',
            ])
            ->assertSessionHas('success');

        $rating = StoreRating::sole();
        $this->assertSame($order->id, $rating->order_id);
        $this->assertSame($buyer->id, $rating->user_id);
        $this->assertSame($order->store_id, $rating->store_id);
        $this->assertSame(5, $rating->rating);
        $this->assertSame('Mantap!', $rating->comment);

        // Second attempt on the same transaction is rejected.
        $this->actingAs($buyer)
            ->from(route('orders.show', $order))
            ->post(route('orders.rate', $order), ['rating' => 1])
            ->assertSessionHas('error');

        $this->assertSame(1, StoreRating::count());
    }

    public function test_order_that_is_not_completed_cannot_be_rated(): void
    {
        $buyer = User::factory()->create(['role' => 'pembeli']);
        $order = Order::factory()->for($buyer, 'user')->create(['status' => Order::STATUS_PENDING_PAYMENT]);

        $this->actingAs($buyer)
            ->post(route('orders.rate', $order), ['rating' => 5])
            ->assertSessionHas('error');

        $this->assertSame(0, StoreRating::count());
    }

    public function test_rating_must_be_between_one_and_five_stars(): void
    {
        $buyer = User::factory()->create(['role' => 'pembeli']);
        $order = Order::factory()->completed()->for($buyer, 'user')->create();

        $this->actingAs($buyer)
            ->post(route('orders.rate', $order), ['rating' => 6])
            ->assertSessionHasErrors(['rating']);

        $this->actingAs($buyer)
            ->post(route('orders.rate', $order), [])
            ->assertSessionHasErrors(['rating']);

        $this->assertSame(0, StoreRating::count());
    }

    public function test_same_buyer_can_rate_the_same_store_again_on_a_new_transaction(): void
    {
        $buyer = User::factory()->create(['role' => 'pembeli']);
        $seller = User::factory()->create(['role' => 'penjual']);
        $store = Store::factory()->for($seller)->create();

        $first = Order::factory()->completed()->for($buyer, 'user')->for($store)->create();
        $second = Order::factory()->completed()->for($buyer, 'user')->for($store)->create();

        $this->actingAs($buyer)->post(route('orders.rate', $first), ['rating' => 5]);
        $this->actingAs($buyer)->post(route('orders.rate', $second), ['rating' => 3, 'comment' => 'Kedua kalinya.']);

        $this->assertSame(2, StoreRating::count());
        $this->assertSame(2, $store->ratings()->count());
        $this->assertEqualsWithDelta(4.0, (float) $store->ratings()->avg('rating'), 0.01);
    }

    public function test_store_detail_shows_no_rating_by_default(): void
    {
        $seller = User::factory()->create(['role' => 'penjual']);
        $store = Store::factory()->for($seller)->create(['slug' => 'warung-belum-dinilai']);

        $this->get(route('store.show', $store))
            ->assertOk()
            ->assertSee('Belum ada rating');
    }

    public function test_store_detail_shows_average_rating_and_count(): void
    {
        $seller = User::factory()->create(['role' => 'penjual']);
        $store = Store::factory()->for($seller)->create(['slug' => 'warung-dinilai']);

        foreach ([5, 4] as $stars) {
            $buyer = User::factory()->create(['role' => 'pembeli']);
            $order = Order::factory()->completed()->for($buyer, 'user')->for($store)->create();
            StoreRating::factory()->for($order)->for($order->user)->for($store)->create([
                'order_id' => $order->id,
                'user_id' => $buyer->id,
                'store_id' => $store->id,
                'rating' => $stars,
            ]);
        }

        $this->get(route('store.show', $store))
            ->assertOk()
            ->assertSee('4,5')
            ->assertSee('2 penilaian')
            ->assertDontSee('Belum ada rating');
    }

    public function test_store_card_on_landing_shows_rating_state(): void
    {
        $seller = User::factory()->create(['role' => 'penjual']);
        $rated = Store::factory()->for($seller)->create(['slug' => 'warung-kartu-dinilai']);
        $unratedSeller = User::factory()->create(['role' => 'penjual']);
        $unrated = Store::factory()->for($unratedSeller)->create(['slug' => 'warung-kartu-kosong']);

        $buyer = User::factory()->create(['role' => 'pembeli']);
        $order = Order::factory()->completed()->for($buyer, 'user')->for($rated)->create();
        StoreRating::factory()->create([
            'order_id' => $order->id,
            'user_id' => $buyer->id,
            'store_id' => $rated->id,
            'rating' => 5,
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('★ 5,0')
            ->assertSee('Belum ada rating');
    }

    public function test_order_detail_shows_rating_form_for_completed_unrated_order(): void
    {
        $buyer = User::factory()->create(['role' => 'pembeli']);
        $order = Order::factory()->completed()->for($buyer, 'user')->create();

        $this->actingAs($buyer)
            ->get(route('orders.show', $order))
            ->assertOk()
            ->assertSee('Beri penilaian warung', false)
            ->assertSee('1 pesanan = 1 penilaian', false)
            ->assertSee(route('orders.rate', $order), false);
    }

    public function test_order_detail_shows_submitted_rating_instead_of_the_form(): void
    {
        $buyer = User::factory()->create(['role' => 'pembeli']);
        $order = Order::factory()->completed()->for($buyer, 'user')->create();
        StoreRating::factory()->create([
            'order_id' => $order->id,
            'user_id' => $buyer->id,
            'store_id' => $order->store_id,
            'rating' => 4,
            'comment' => 'Enak.',
        ]);

        $this->actingAs($buyer)
            ->get(route('orders.show', $order))
            ->assertOk()
            ->assertSee('Penilaianmu')
            ->assertSee('Enak.')
            ->assertDontSee('Beri penilaian warung', false);
    }

    public function test_seeder_rates_completed_orders_and_leaves_one_store_unrated(): void
    {
        $this->seed();

        $this->assertGreaterThan(0, StoreRating::count());

        // Every rating maps to a completed order (1 transaction = 1 rating).
        $this->assertSame(
            0,
            StoreRating::whereHas('order', fn ($query) => $query->where('status', '!=', Order::STATUS_COMPLETED))->count(),
        );

        $this->assertSame(
            StoreRating::count(),
            StoreRating::distinct()->count('order_id'),
        );

        // kedai-kopi-hebat is intentionally left without ratings.
        $unrated = Store::where('slug', 'kedai-kopi-hebat')->first();
        $this->assertNotNull($unrated);
        $this->assertSame(0, $unrated->ratings()->count());
        $this->assertNull($unrated->ratingAverage());

        // dewi rated warung-bang-jago across multiple transactions.
        $bangJago = Store::where('slug', 'warung-bang-jago')->first();
        $dewi = User::where('email', 'dewi@example.com')->first();
        $this->assertGreaterThanOrEqual(2, $bangJago->ratings()->where('user_id', $dewi->id)->count());
    }
}
