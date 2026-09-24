<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InStoreTransaction extends Model
{
    public const BUYER_REGISTERED = 'registered';

    public const BUYER_WALK_IN = 'walk_in';

    protected $fillable = [
        'store_id',
        'buyer_user_id',
        'buyer_type',
        'buyer_name',
        'buyer_email',
        'buyer_phone',
        'total',
    ];

    protected function casts(): array
    {
        return [
            'total' => 'integer',
        ];
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_user_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(InStoreTransactionItem::class);
    }

    /**
     * Record a completed counter sale and decrement inventory atomically.
     *
     * @param  array<int, array{product_id: int, qty: int}>  $lines
     * @param  array{name?: ?string, email?: ?string, phone?: ?string}  $buyerDetails
     */
    public static function recordSale(Store $store, array $lines, string $buyerType, ?User $buyer, array $buyerDetails): self
    {
        if ($lines === []) {
            throw ValidationException::withMessages(['items' => 'Tambahkan minimal satu produk.']);
        }

        return DB::transaction(function () use ($store, $lines, $buyerType, $buyer, $buyerDetails): self {
            $productIds = collect($lines)->pluck('product_id')->map(fn ($id): int => (int) $id)->sort()->values();
            $products = Product::query()
                ->where('user_id', $store->user_id)
                ->whereIn('id', $productIds)
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            if ($products->count() !== $productIds->unique()->count()) {
                throw ValidationException::withMessages(['items' => 'Produk tidak ditemukan di warungmu.']);
            }

            $preparedLines = collect($lines)->map(function (array $line) use ($products): array {
                $product = $products->get((int) $line['product_id']);
                $qty = (int) $line['qty'];

                if (! $product->isApproved()) {
                    throw ValidationException::withMessages(['items' => "Produk \"{$product->name}\" belum disetujui untuk dijual."]);
                }

                if ($product->stock < $qty) {
                    throw ValidationException::withMessages(['items' => "Stok \"{$product->name}\" hanya {$product->stock} pcs."]);
                }

                $price = $product->effectivePrice();

                return [
                    'product' => $product,
                    'qty' => $qty,
                    'price' => $price,
                    'subtotal' => $price * $qty,
                ];
            });

            $transaction = $store->inStoreTransactions()->create([
                'buyer_user_id' => $buyer?->id,
                'buyer_type' => $buyerType,
                'buyer_name' => $buyer?->name ?? ($buyerDetails['name'] ?? null),
                'buyer_email' => $buyer?->email ?? ($buyerDetails['email'] ?? null),
                'buyer_phone' => $buyer?->phone ?? ($buyerDetails['phone'] ?? null),
                'total' => (int) $preparedLines->sum('subtotal'),
            ]);

            foreach ($preparedLines as $line) {
                $transaction->items()->create([
                    'product_id' => $line['product']->id,
                    'name' => $line['product']->name,
                    'price' => $line['price'],
                    'qty' => $line['qty'],
                    'subtotal' => $line['subtotal'],
                ]);

                $line['product']->decrement('stock', $line['qty']);
            }

            return $transaction;
        });
    }
}
