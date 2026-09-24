<?php

namespace Tests\Feature;

use App\Models\SellerVerification;
use App\Models\Setting;
use App\Models\Store;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Models\Withdrawal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WithdrawalTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_from_the_wallet(): void
    {
        $this->get(route('seller.wallet.index'))->assertRedirect(route('login'));
    }

    public function test_buyer_cannot_open_the_seller_wallet(): void
    {
        $buyer = User::factory()->create(['role' => 'pembeli']);

        $this->actingAs($buyer)
            ->get(route('seller.wallet.index'))
            ->assertRedirect(route('dashboard'));
    }

    public function test_seller_requesting_a_withdrawal_holds_the_money(): void
    {
        [$seller, $wallet] = $this->sellerWithBalance(100000);

        $this->actingAs($seller)
            ->post(route('seller.wallet.withdraw'), [
                'amount' => 50000,
                'bank_name' => 'BCA',
                'account_number' => '1234567890',
                'account_name' => 'Budi Santoso',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $withdrawal = $wallet->withdrawals()->sole();

        $this->assertSame(50000, $withdrawal->amount);
        $this->assertSame(Withdrawal::STATUS_PENDING, $withdrawal->status);
        $this->assertSame('BCA', $withdrawal->bank_name);
        $this->assertSame(50000, $wallet->fresh()->balance);

        $transaction = $wallet->transactions()->sole();

        $this->assertSame(WalletTransaction::TYPE_DEBIT, $transaction->type);
        $this->assertSame(50000, $transaction->amount);
        $this->assertSame(50000, $transaction->balance_after);
        $this->assertSame($withdrawal->id, $transaction->reference_id);
    }

    public function test_withdrawal_below_the_minimum_is_rejected(): void
    {
        Setting::setValue(Setting::WITHDRAWAL_MIN, 10000);

        [$seller, $wallet] = $this->sellerWithBalance(100000);

        $this->actingAs($seller)
            ->post(route('seller.wallet.withdraw'), [
                'amount' => 5000,
                'bank_name' => 'BCA',
                'account_number' => '1234567890',
                'account_name' => 'Budi Santoso',
            ])
            ->assertSessionHasErrors(['amount']);

        $this->assertSame(0, $wallet->withdrawals()->count());
        $this->assertSame(100000, $wallet->fresh()->balance);
    }

    public function test_withdrawal_above_the_maximum_is_rejected(): void
    {
        Setting::setValue(Setting::WITHDRAWAL_MAX, 200000);

        [$seller, $wallet] = $this->sellerWithBalance(5000000);

        $this->actingAs($seller)
            ->post(route('seller.wallet.withdraw'), [
                'amount' => 3000000,
                'bank_name' => 'BCA',
                'account_number' => '1234567890',
                'account_name' => 'Budi Santoso',
            ])
            ->assertSessionHasErrors(['amount']);

        $this->assertSame(0, $wallet->withdrawals()->count());
        $this->assertSame(5000000, $wallet->fresh()->balance);
    }

    public function test_withdrawal_above_the_balance_is_rejected(): void
    {
        [$seller, $wallet] = $this->sellerWithBalance(20000);

        $this->actingAs($seller)
            ->post(route('seller.wallet.withdraw'), [
                'amount' => 50000,
                'bank_name' => 'BCA',
                'account_number' => '1234567890',
                'account_name' => 'Budi Santoso',
            ])
            ->assertSessionHasErrors(['amount']);

        $this->assertSame(0, $wallet->withdrawals()->count());
        $this->assertSame(20000, $wallet->fresh()->balance);
    }

    public function test_money_input_accepts_thousand_separators(): void
    {
        [$seller, $wallet] = $this->sellerWithBalance(100000);

        $this->actingAs($seller)
            ->post(route('seller.wallet.withdraw'), [
                'amount' => '50.000',
                'bank_name' => 'BCA',
                'account_number' => '1234567890',
                'account_name' => 'Budi Santoso',
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame(50000, $wallet->withdrawals()->value('amount'));
    }

    public function test_admin_can_mark_a_withdrawal_as_paid(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        [$seller, $wallet] = $this->sellerWithBalance(100000);

        $this->actingAs($seller)->post(route('seller.wallet.withdraw'), [
            'amount' => 50000,
            'bank_name' => 'BCA',
            'account_number' => '1234567890',
            'account_name' => 'Budi Santoso',
        ]);

        $withdrawal = $wallet->withdrawals()->sole();

        $this->actingAs($admin)
            ->patch(route('admin.withdrawals.paid', $withdrawal), ['admin_note' => 'Transfer via BCA.'])
            ->assertRedirect()
            ->assertSessionHas('success');

        $withdrawal->refresh();

        $this->assertSame(Withdrawal::STATUS_PAID, $withdrawal->status);
        $this->assertSame($admin->id, $withdrawal->processed_by);
        $this->assertSame('Transfer via BCA.', $withdrawal->admin_note);
        $this->assertNotNull($withdrawal->processed_at);

        // Paying out keeps the money out of the wallet.
        $this->assertSame(50000, $wallet->fresh()->balance);
        $this->assertSame(1, $wallet->transactions()->count());
    }

    public function test_admin_rejecting_a_withdrawal_returns_the_money(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        [$seller, $wallet] = $this->sellerWithBalance(100000);

        $this->actingAs($seller)->post(route('seller.wallet.withdraw'), [
            'amount' => 50000,
            'bank_name' => 'BCA',
            'account_number' => '1234567890',
            'account_name' => 'Budi Santoso',
        ]);

        $withdrawal = $wallet->withdrawals()->sole();

        $this->actingAs($admin)
            ->patch(route('admin.withdrawals.reject', $withdrawal), [
                'admin_note' => 'Nama rekening tidak cocok.',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame(Withdrawal::STATUS_REJECTED, $withdrawal->fresh()->status);
        $this->assertSame(100000, $wallet->fresh()->balance);

        $refund = $wallet->transactions()->first();

        $this->assertSame(WalletTransaction::TYPE_CREDIT, $refund->type);
        $this->assertSame(50000, $refund->amount);
        $this->assertSame(100000, $refund->balance_after);
    }

    public function test_rejecting_a_withdrawal_requires_a_reason(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $withdrawal = Withdrawal::factory()->create();

        $this->actingAs($admin)
            ->patch(route('admin.withdrawals.reject', $withdrawal), ['admin_note' => ''])
            ->assertSessionHasErrors(['admin_note']);

        $this->assertSame(Withdrawal::STATUS_PENDING, $withdrawal->fresh()->status);
    }

    public function test_a_processed_withdrawal_cannot_be_processed_again(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        [$seller, $wallet] = $this->sellerWithBalance(100000);

        $this->actingAs($seller)->post(route('seller.wallet.withdraw'), [
            'amount' => 50000,
            'bank_name' => 'BCA',
            'account_number' => '1234567890',
            'account_name' => 'Budi Santoso',
        ]);

        $withdrawal = $wallet->withdrawals()->sole();

        $this->actingAs($admin)->patch(route('admin.withdrawals.paid', $withdrawal));

        $this->actingAs($admin)
            ->patch(route('admin.withdrawals.reject', $withdrawal), ['admin_note' => 'Batal.'])
            ->assertSessionHas('error');

        $this->assertSame(Withdrawal::STATUS_PAID, $withdrawal->fresh()->status);
        $this->assertSame(50000, $wallet->fresh()->balance);
        $this->assertSame(1, $wallet->transactions()->count());
    }

    public function test_seller_cannot_withdraw_another_stores_wallet(): void
    {
        $otherSeller = User::factory()->create(['role' => 'penjual']);
        $otherStore = Store::factory()->for($otherSeller)->create();
        Wallet::factory()->for($otherStore)->withBalance(500000)->create();

        $seller = User::factory()->create(['role' => 'penjual']);
        SellerVerification::factory()->for($seller)->verified()->create();
        Store::factory()->for($seller)->create();

        $this->actingAs($seller)
            ->post(route('seller.wallet.withdraw'), [
                'amount' => 50000,
                'bank_name' => 'BCA',
                'account_number' => '1234567890',
                'account_name' => 'Budi Santoso',
            ])
            ->assertSessionHasErrors(['amount']);

        $this->assertSame(0, $otherStore->wallet->withdrawals()->count());
        $this->assertSame(500000, $otherStore->wallet->fresh()->balance);
    }

    public function test_admin_can_open_the_withdrawal_queue(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        [$seller, $wallet] = $this->sellerWithBalance(100000);

        $this->actingAs($seller)->post(route('seller.wallet.withdraw'), [
            'amount' => 50000,
            'bank_name' => 'BCA',
            'account_number' => '1234567890',
            'account_name' => 'Budi Santoso',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.withdrawals'))
            ->assertOk()
            ->assertSee('Penarikan mitra')
            ->assertSee('Budi Santoso')
            ->assertSee('Rp 50.000');
    }

    /** @return array{0: User, 1: Wallet} */
    private function sellerWithBalance(int $balance): array
    {
        $seller = User::factory()->create(['role' => 'penjual']);
        SellerVerification::factory()->for($seller)->verified()->create();
        $store = Store::factory()->for($seller)->create();
        $wallet = Wallet::factory()->for($store)->withBalance($balance)->create();

        return [$seller, $wallet];
    }
}
