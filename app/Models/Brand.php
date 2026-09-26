<?php

namespace App\Models;

use Database\Factories\BrandFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Brand extends Model
{
    /** @use HasFactory<BrandFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'slug',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /** Brands belong to one seller — slugs only need to be unique per seller. */
    public static function uniqueSlugForUser(int $userId, string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'brand';
        $base = substr($base, 0, 95);
        $slug = $base;
        $i = 2;

        while (static::where('user_id', $userId)->where('slug', $slug)->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))->exists()) {
            $suffix = '-'.$i++;
            $slug = substr($base, 0, 100 - strlen($suffix)).$suffix;
        }

        return $slug;
    }
}
