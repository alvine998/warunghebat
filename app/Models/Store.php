<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Store extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'description',
        'address',
        'latitude',
        'longitude',
        'phone',
        'open_time',
        'close_time',
        'is_open',
        'image_path',
    ];

    protected function casts(): array
    {
        return [
            'is_open' => 'boolean',
        ];
    }

    /** Public storefront URLs resolve by slug (/w/{slug}). */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Products sold by this store's owner. ponytail: add store_id FK when catalog grows. */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'user_id', 'user_id');
    }

    /** One store per seller; auto-provisioned on first access. */
    public static function resolveFor(User $user): Store
    {
        return $user->store()->firstOrCreate(
            [],
            [
                'name' => $user->name.' — Warung',
                'slug' => static::uniqueSlug($user->name.' warung'),
                'is_open' => true,
            ],
        );
    }

    /** Slug mirrors name; suffix -2, -3… on collision. */
    public static function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'warung';
        $base = substr($base, 0, 75);
        $slug = $base;
        $i = 2;

        while (static::where('slug', $slug)->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))->exists()) {
            $suffix = '-'.$i++;
            $slug = substr($base, 0, 80 - strlen($suffix)).$suffix;
        }

        return $slug;
    }

    public function getImageUrlAttribute(): ?string
    {
        if (! $this->image_path) {
            return null;
        }

        return asset('storage/'.ltrim($this->image_path, '/'));
    }

    public function getHoursLabelAttribute(): ?string
    {
        if (! $this->open_time || ! $this->close_time) {
            return null;
        }

        return substr((string) $this->open_time, 0, 5).' – '.substr((string) $this->close_time, 0, 5);
    }
}
