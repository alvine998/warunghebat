<?php

namespace App\Models;

use Database\Factories\PaymentMethodFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentMethod extends Model
{
    /** @use HasFactory<PaymentMethodFactory> */
    use HasFactory;

    public const TYPE_BANK = 'bank';

    public const TYPE_QRIS = 'qris';

    public const TYPE_EWALLET = 'ewallet';

    public const TYPES = [self::TYPE_BANK, self::TYPE_QRIS, self::TYPE_EWALLET];

    public const TYPE_LABELS = [
        self::TYPE_BANK => 'Transfer Bank',
        self::TYPE_QRIS => 'QRIS',
        self::TYPE_EWALLET => 'E-Wallet',
    ];

    protected $fillable = [
        'type',
        'name',
        'account_number',
        'account_name',
        'instructions',
        'image_path',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /** The methods a buyer may pay into, in the order the admin arranged them. */
    #[Scope]
    protected function active(Builder $query): void
    {
        $query->where('is_active', true)->orderBy('sort_order')->orderBy('id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function getImageUrlAttribute(): ?string
    {
        if (! $this->image_path) {
            return null;
        }

        return asset('storage/'.ltrim($this->image_path, '/'));
    }

    public function typeLabel(): string
    {
        return self::TYPE_LABELS[$this->type] ?? $this->type;
    }

    public function isQris(): bool
    {
        return $this->type === self::TYPE_QRIS;
    }
}
