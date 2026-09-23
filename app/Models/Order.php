<?php

namespace App\Models;

use Database\Factories\OrderFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class Order extends Model
{
    /** @use HasFactory<OrderFactory> */
    use HasFactory;

    public const STATUS_PENDING_PAYMENT = 'pending_payment';

    public const STATUS_WAITING_VERIFICATION = 'waiting_verification';

    public const STATUS_PAID = 'paid';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUSES = [
        self::STATUS_PENDING_PAYMENT,
        self::STATUS_WAITING_VERIFICATION,
        self::STATUS_PAID,
        self::STATUS_COMPLETED,
        self::STATUS_CANCELLED,
    ];

    public const STATUS_LABELS = [
        self::STATUS_PENDING_PAYMENT => 'Menunggu pembayaran',
        self::STATUS_WAITING_VERIFICATION => 'Menunggu verifikasi',
        self::STATUS_PAID => 'Dibayar — dana ditahan',
        self::STATUS_COMPLETED => 'Selesai',
        self::STATUS_CANCELLED => 'Dibatalkan',
    ];

    /** Admin may still release or refund these. */
    public const CANCELLABLE_STATUSES = [
        self::STATUS_PENDING_PAYMENT,
        self::STATUS_WAITING_VERIFICATION,
    ];

    protected $fillable = [
        'user_id',
        'store_id',
        'warung_name',
        'item_name',
        'icon',
        'total',
        'subtotal',
        'commission_amount',
        'status',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'total' => 'integer',
            'subtotal' => 'integer',
            'commission_amount' => 'integer',
            'completed_at' => 'datetime',
        ];
    }

    /** The buyer. */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class)->latest('id');
    }

    public function latestPayment(): HasOne
    {
        return $this->hasOne(Payment::class)->latestOfMany();
    }

    public function code(): string
    {
        return 'WH-'.str_pad((string) $this->getKey(), 5, '0', STR_PAD_LEFT);
    }

    /** Resolves a typed code ("WH-00012") back to the order id for searching. */
    public static function idFromCode(string $search): ?int
    {
        if (! preg_match('/^wh-?0*(\d+)$/i', trim($search), $matches)) {
            return null;
        }

        return (int) $matches[1];
    }

    public function statusLabel(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    /** Buyer paid and the platform is holding the money. */
    public function isPaid(): bool
    {
        return $this->status === self::STATUS_PAID;
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, self::CANCELLABLE_STATUSES, true);
    }

    public function markAwaitingVerification(): void
    {
        $this->update(['status' => self::STATUS_WAITING_VERIFICATION]);
    }

    public function markPaid(): void
    {
        $this->update(['status' => self::STATUS_PAID]);
    }

    /**
     * Escrow release: withholds the platform commission, credits the selling store's
     * wallet and closes the order. Returns null when the order holds no funds.
     */
    public function releaseFunds(): ?WalletTransaction
    {
        if (! $this->isPaid() || ! $this->store) {
            return null;
        }

        return DB::transaction(function (): WalletTransaction {
            $commission = (int) round($this->total * Setting::commissionPercent() / 100);

            $transaction = Wallet::forStore($this->store)->credit(
                $this->total - $commission,
                "Pesanan {$this->code()} — {$this->store->name}",
                $this,
            );

            $this->update([
                'status' => self::STATUS_COMPLETED,
                'commission_amount' => $commission,
                'completed_at' => now(),
            ]);

            return $transaction;
        });
    }

    /** Cancels an order that has not been paid out and returns its reserved stock. */
    public function cancel(): void
    {
        if (! $this->canBeCancelled()) {
            return;
        }

        DB::transaction(function (): void {
            foreach ($this->items()->whereNotNull('product_id')->get() as $item) {
                Product::whereKey($item->product_id)->increment('stock', $item->qty);
            }

            $this->update(['status' => self::STATUS_CANCELLED]);
        });
    }

    /**
     * Turns the session cart into an order, reserving stock as it goes.
     *
     * @param  array{store_id?: int|string, items?: array<int|string, array{product_id?: int|string, qty?: int|string}>}  $cart
     */
    public static function placeFromCart(array $cart, User $buyer): self
    {
        $store = Store::find($cart['store_id'] ?? null);
        $lines = $cart['items'] ?? [];

        if (! $store || ! is_array($lines) || $lines === []) {
            throw ValidationException::withMessages(['cart' => 'Keranjang masih kosong.']);
        }

        if (! $store->is_open) {
            throw ValidationException::withMessages(['cart' => "Warung \"{$store->name}\" sedang tutup."]);
        }

        return DB::transaction(function () use ($store, $lines, $buyer): self {
            $lines = collect($lines)->map(function ($line): array {
                $qty = (int) ($line['qty'] ?? 0);

                $product = Product::query()
                    ->whereKey($line['product_id'] ?? null)
                    ->lockForUpdate()
                    ->first();

                if (! $product || ! $product->isApproved()) {
                    throw ValidationException::withMessages(['cart' => 'Ada produk di keranjang yang sudah tidak tayang.']);
                }

                if ($product->stock < $qty) {
                    throw ValidationException::withMessages(['cart' => "Stok \"{$product->name}\" tinggal {$product->stock} pcs."]);
                }

                return [
                    'product' => $product,
                    'qty' => $qty,
                    'subtotal' => $product->price * $qty,
                ];
            });

            $subtotal = (int) $lines->sum('subtotal');
            $names = $lines->pluck('product.name')->all();

            $order = $buyer->orders()->create([
                'store_id' => $store->id,
                'warung_name' => $store->name,
                'item_name' => count($names) === 1 ? $names[0] : $names[0].' +'.(count($names) - 1).' lainnya',
                'icon' => '🛒',
                'subtotal' => $subtotal,
                'total' => $subtotal,
                'status' => self::STATUS_PENDING_PAYMENT,
            ]);

            foreach ($lines as $line) {
                $order->items()->create([
                    'product_id' => $line['product']->id,
                    'name' => $line['product']->name,
                    'price' => $line['product']->price,
                    'qty' => $line['qty'],
                    'subtotal' => $line['subtotal'],
                ]);

                $line['product']->decrement('stock', $line['qty']);
            }

            return $order;
        });
    }
}
