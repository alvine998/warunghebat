<?php

namespace App\Models;

use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'price',
        'discount_price',
        'promo_starts_at',
        'promo_ends_at',
        'stock',
        'category',
        'image_path',
        'status',
        'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'discount_price' => 'integer',
            'promo_starts_at' => 'datetime',
            'promo_ends_at' => 'datetime',
            'stock' => 'integer',
        ];
    }

    public const STATUSES = ['pending', 'approved', 'rejected'];

    public const CATEGORIES = [
        'Makanan',
        'Minuman',
        'Sembako',
        'Jajanan',
        'Frozen',
        'Harian',
        'Lainnya',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getImageUrlAttribute(): ?string
    {
        if (! $this->image_path) {
            return null;
        }

        return asset('storage/'.ltrim($this->image_path, '/'));
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * A promo is live when a discount price is set below the regular price
     * and the optional promo window covers right now. No dates means the
     * discount runs until the seller removes it.
     */
    public function hasActivePromo(?Carbon $at = null): bool
    {
        $at ??= now();

        if ($this->discount_price === null || $this->discount_price >= $this->price) {
            return false;
        }

        if ($this->promo_starts_at !== null && $at->lt($this->promo_starts_at)) {
            return false;
        }

        if ($this->promo_ends_at !== null && $at->gt($this->promo_ends_at)) {
            return false;
        }

        return true;
    }

    /** What the buyer actually pays — the promo price while it is live. */
    public function effectivePrice(): int
    {
        return $this->hasActivePromo() ? (int) $this->discount_price : (int) $this->price;
    }

    /** Whole-percent discount while a promo is live, null otherwise. */
    public function discountPercent(): ?int
    {
        if (! $this->hasActivePromo() || $this->price <= 0) {
            return null;
        }

        return (int) round(($this->price - $this->discount_price) / $this->price * 100);
    }

    /**
     * Products whose promo is live right now: approved, in stock, discounted
     * below the regular price, and inside the optional promo window.
     */
    public function scopeWithActivePromo(Builder $query, ?Carbon $at = null): Builder
    {
        $at ??= now();

        return $query
            ->where('status', 'approved')
            ->where('stock', '>', 0)
            ->whereNotNull('discount_price')
            ->whereColumn('discount_price', '<', 'price')
            ->where(fn (Builder $query) => $query->whereNull('promo_starts_at')->orWhere('promo_starts_at', '<=', $at))
            ->where(fn (Builder $query) => $query->whereNull('promo_ends_at')->orWhere('promo_ends_at', '>=', $at));
    }
}
