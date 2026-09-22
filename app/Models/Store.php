<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Store extends Model
{
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** One store per seller; auto-provisioned on first access. */
    public static function resolveFor(User $user): Store
    {
        $base = Str::slug($user->name.'-warung') ?: 'warung-'.$user->id;
        $slug = $base;
        $i = 2;
        while (static::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $user->store()->firstOrCreate(
            [],
            [
                'name' => $user->name.' — Warung',
                'slug' => $slug,
                'is_open' => true,
            ],
        );
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
