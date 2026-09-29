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
        'cost_price',
        'discount_price',
        'promo_starts_at',
        'promo_ends_at',
        'stock',
        'barcode',
        'category',
        'category_id',
        'brand_id',
        'image_path',
        'status',
        'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'cost_price' => 'integer',
            'discount_price' => 'integer',
            'promo_starts_at' => 'datetime',
            'promo_ends_at' => 'datetime',
            'stock' => 'integer',
        ];
    }

    public const STATUSES = ['pending', 'approved', 'rejected'];

    /**
     * Legacy hardcoded list — kept so old factories/tests that pass a
     * category string keep working. New code must use categories table.
     */
    public const CATEGORIES = [
        'Makanan',
        'Minuman',
        'Sembako',
        'Jajanan',
        'Frozen',
        'Harian',
        'Lainnya',
    ];

    protected static function booted(): void
    {
        // Keep the legacy `category` string and the new `category_id` in sync
        // so old rows, factories, and tests keep working during migration.
        // Optimized: single lookup, skipped entirely when both sides already agree.
        static::saving(function (Product $product): void {
            if ($product->category_id && $product->category) {
                // Fast path — controller already sets both consistently. Avoid any query.
                // Only re-sync when the FK itself changed (rename handled by admin sync job).
                if ($product->isDirty('category_id')) {
                    $name = Category::whereKey($product->category_id)->value('name');
                    if (is_string($name) && $name !== '') {
                        $product->category = $name;
                    }
                }

                return;
            }

            if ($product->category_id && ! $product->category) {
                $product->category = Category::whereKey($product->category_id)->value('name');
            } elseif ($product->category && ! $product->category_id) {
                $product->category_id = Category::where('name', $product->category)->value('id');
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function categoryRef(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    /** Display name: relation first, legacy string column as fallback. */
    public function categoryName(): string
    {
        return $this->categoryRef->name ?? $this->category ?? 'Lainnya';
    }

    public function brandName(): ?string
    {
        return $this->brand->name ?? null;
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

    /**
     * Estimated profit per pcs (Harga Jual efektif − HPP).
     * Null when HPP is unknown. Private to the seller — never shown to buyers.
     */
    public function estimatedProfit(): ?int
    {
        if ($this->cost_price === null) {
            return null;
        }

        return $this->effectivePrice() - (int) $this->cost_price;
    }

    /** Whole-percent margin on the effective selling price, null when HPP is unknown. */
    public function profitMarginPercent(): ?int
    {
        if ($this->cost_price === null) {
            return null;
        }

        $effective = $this->effectivePrice();

        if ($effective <= 0) {
            return null;
        }

        return (int) round(($effective - (int) $this->cost_price) / $effective * 100);
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
