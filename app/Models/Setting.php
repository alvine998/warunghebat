<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    public const WITHDRAWAL_MIN = 'withdrawal_min';

    public const WITHDRAWAL_MAX = 'withdrawal_max';

    public const COMMISSION_PERCENT = 'commission_percent';

    /** Used until an admin saves the settings screen for the first time. */
    public const DEFAULTS = [
        self::WITHDRAWAL_MIN => 10000,
        self::WITHDRAWAL_MAX => 5000000,
        self::COMMISSION_PERCENT => 0,
    ];

    private const CACHE_KEY = 'settings.values';

    protected $fillable = [
        'key',
        'value',
    ];

    public static function withdrawalMin(): int
    {
        return static::asInt(self::WITHDRAWAL_MIN);
    }

    public static function withdrawalMax(): int
    {
        return static::asInt(self::WITHDRAWAL_MAX);
    }

    public static function commissionPercent(): int
    {
        return static::asInt(self::COMMISSION_PERCENT);
    }

    public static function setValue(string $key, int|string|null $value): void
    {
        static::updateOrCreate(
            ['key' => $key],
            ['value' => $value === null ? null : (string) $value],
        );

        Cache::forget(self::CACHE_KEY);
    }

    /** @return array<string, string|null> */
    public static function allValues(): array
    {
        return Cache::rememberForever(
            self::CACHE_KEY,
            fn (): array => static::query()->pluck('value', 'key')->all(),
        );
    }

    private static function asInt(string $key): int
    {
        $value = static::allValues()[$key] ?? null;

        return is_numeric($value) ? (int) $value : (int) self::DEFAULTS[$key];
    }
}
