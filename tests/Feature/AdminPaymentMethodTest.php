<?php

namespace Tests\Feature;

use App\Models\PaymentMethod;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminPaymentMethodTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_the_admin_login(): void
    {
        // The auth middleware runs before the admin middleware, so a guest is
        // sent to the sign-in page rather than the backoffice login.
        $this->get(route('admin.payment-methods'))->assertRedirect(route('login'));
    }

    public function test_non_admin_cannot_create_a_payment_method(): void
    {
        $buyer = User::factory()->create(['role' => 'pembeli']);

        $this->actingAs($buyer)
            ->post(route('admin.payment-methods.store'), [
                'type' => 'bank',
                'name' => 'BCA a/n Warung Hebat',
                'account_number' => '1234567890',
            ])
            ->assertRedirect(route('dashboard'));

        $this->assertSame(0, PaymentMethod::count());
    }

    public function test_admin_can_create_a_bank_payment_method(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->post(route('admin.payment-methods.store'), [
                'type' => 'bank',
                'name' => 'BCA a/n Warung Hebat',
                'account_number' => '1234567890',
                'account_name' => 'PT Warung Hebat',
                'instructions' => 'Transfer sesuai nominal pesanan.',
                'is_active' => '1',
                'sort_order' => 2,
            ])
            ->assertRedirect(route('admin.payment-methods'))
            ->assertSessionHas('success');

        $method = PaymentMethod::sole();

        $this->assertSame('bank', $method->type);
        $this->assertSame('BCA a/n Warung Hebat', $method->name);
        $this->assertSame('1234567890', $method->account_number);
        $this->assertTrue($method->is_active);
        $this->assertSame(2, $method->sort_order);
    }

    public function test_account_number_is_required_for_a_bank_transfer(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->post(route('admin.payment-methods.store'), [
                'type' => 'bank',
                'name' => 'BCA a/n Warung Hebat',
            ])
            ->assertSessionHasErrors(['account_number']);

        $this->assertSame(0, PaymentMethod::count());
    }

    public function test_qris_method_can_be_saved_without_an_account_number(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->post(route('admin.payment-methods.store'), [
                'type' => 'qris',
                'name' => 'QRIS Warung Hebat',
                'image' => UploadedFile::fake()->image('qris.png'),
            ])
            ->assertSessionHasNoErrors();

        $method = PaymentMethod::sole();

        $this->assertSame('qris', $method->type);
        $this->assertNull($method->account_number);
        Storage::disk('public')->assertExists($method->image_path);
    }

    public function test_an_invalid_type_is_rejected(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->post(route('admin.payment-methods.store'), [
                'type' => 'crypto',
                'name' => 'Bitcoin',
                'account_number' => '123',
            ])
            ->assertSessionHasErrors(['type']);

        $this->assertSame(0, PaymentMethod::count());
    }

    public function test_admin_can_update_a_payment_method(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $method = PaymentMethod::factory()->create(['name' => 'BCA a/n Warung Hebat']);

        $this->actingAs($admin)
            ->put(route('admin.payment-methods.update', $method), [
                'type' => 'bank',
                'name' => 'BCA a/n PT Warung Hebat',
                'account_number' => '999888777',
                'is_active' => '1',
                'sort_order' => 1,
            ])
            ->assertRedirect(route('admin.payment-methods'))
            ->assertSessionHas('success');

        $method->refresh();

        $this->assertSame('BCA a/n PT Warung Hebat', $method->name);
        $this->assertSame('999888777', $method->account_number);
    }

    public function test_admin_can_toggle_a_payment_method(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $method = PaymentMethod::factory()->create(['is_active' => true]);

        $this->actingAs($admin)
            ->patch(route('admin.payment-methods.toggle', $method))
            ->assertSessionHas('success');

        $this->assertFalse($method->fresh()->is_active);

        $this->actingAs($admin)
            ->patch(route('admin.payment-methods.toggle', $method));

        $this->assertTrue($method->fresh()->is_active);
    }

    public function test_deleting_a_payment_method_removes_its_image(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create(['role' => 'admin']);
        Storage::disk('public')->put('payment-methods/qris.png', 'fake-image');

        $method = PaymentMethod::factory()->qris()->create(['image_path' => 'payment-methods/qris.png']);

        $this->actingAs($admin)
            ->delete(route('admin.payment-methods.destroy', $method))
            ->assertSessionHas('success');

        $this->assertSame(0, PaymentMethod::count());
        Storage::disk('public')->assertMissing('payment-methods/qris.png');
    }

    public function test_only_active_methods_are_offered_to_buyers(): void
    {
        PaymentMethod::factory()->create(['name' => 'Bank Aktif', 'is_active' => true, 'sort_order' => 2]);
        PaymentMethod::factory()->create(['name' => 'Bank Nonaktif', 'is_active' => false]);
        PaymentMethod::factory()->create(['name' => 'Bank Prioritas', 'is_active' => true, 'sort_order' => 1]);

        $methods = PaymentMethod::active()->get();

        $this->assertCount(2, $methods);
        $this->assertSame(['Bank Prioritas', 'Bank Aktif'], $methods->pluck('name')->all());
    }

    public function test_admin_can_open_the_payment_methods_pages(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $method = PaymentMethod::factory()->qris()->create(['name' => 'QRIS Warung Hebat']);

        $this->actingAs($admin)
            ->get(route('admin.payment-methods'))
            ->assertOk()
            ->assertSee('Rekening Warung Hebat')
            ->assertSee('QRIS Warung Hebat');

        $this->actingAs($admin)
            ->get(route('admin.payment-methods.create'))
            ->assertOk()
            ->assertSee('Tambah metode');

        $this->actingAs($admin)
            ->get(route('admin.payment-methods.edit', $method))
            ->assertOk()
            ->assertSee('Edit metode')
            ->assertSee('QRIS Warung Hebat');
    }
}
