<?php

namespace App\Models;

use Database\Factories\WalletFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class Wallet extends Model
{
    /** @use HasFactory<WalletFactory> */
    use HasFactory;

    protected $fillable = [
        'store_id',
        'balance',
    ];

    /** Mirrors the column default so a freshly provisioned wallet reads 0. */
    protected $attributes = [
        'balance' => 0,
    ];

    protected function casts(): array
    {
        return [
            'balance' => 'integer',
        ];
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(WalletTransaction::class)->latest('id');
    }

    public function withdrawals(): HasMany
    {
        return $this->hasMany(Withdrawal::class)->latest('id');
    }

    /** One wallet per store, provisioned on first use like Store::resolveFor(). */
    public static function forStore(Store $store): self
    {
        return $store->wallet()->firstOrCreate([]);
    }

    /** Order money released from escrow into the store's wallet. */
    public function credit(int $amount, string $description, ?Model $reference = null): WalletTransaction
    {
        return $this->record(WalletTransaction::TYPE_CREDIT, $amount, $description, $reference);
    }

    /** Money leaving the wallet (a withdrawal request being held). */
    public function debit(int $amount, string $description, ?Model $reference = null): WalletTransaction
    {
        return $this->record(WalletTransaction::TYPE_DEBIT, $amount, $description, $reference);
    }

    public function hasBalance(int $amount): bool
    {
        return $this->balance >= $amount;
    }

    /**
     * Moves the balance and writes the ledger row under a row lock, so two
     * concurrent payouts can never both read the same starting balance.
     */
    private function record(string $type, int $amount, string $description, ?Model $reference): WalletTransaction
    {
        return DB::transaction(function () use ($type, $amount, $description, $reference): WalletTransaction {
            $wallet = static::query()->whereKey($this->getKey())->lockForUpdate()->firstOrFail();

            $balance = $type === WalletTransaction::TYPE_CREDIT
                ? $wallet->balance + $amount
                : $wallet->balance - $amount;

            if ($balance < 0) {
                throw ValidationException::withMessages([
                    'amount' => 'Saldo warung tidak cukup untuk penarikan ini.',
                ]);
            }

            $wallet->update(['balance' => $balance]);
            $this->setAttribute('balance', $balance);

            return $wallet->transactions()->create([
                'type' => $type,
                'amount' => $amount,
                'balance_after' => $balance,
                'description' => $description,
                'reference_type' => $reference?->getMorphClass(),
                'reference_id' => $reference?->getKey(),
            ]);
        });
    }
}
