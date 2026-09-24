<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    public const WITHDRAWAL_MIN = 'withdrawal_min';

    public const WITHDRAWAL_MAX = 'withdrawal_max';

    public const COMMISSION_PERCENT = 'commission_percent';

    public const CS_WHATSAPP = 'cs_whatsapp';

    public const OFFICIAL_EMAIL = 'official_email';

    public const OFFICE_ADDRESS = 'office_address';

    public const OPERATIONAL_DAYS = 'operational_days';

    public const OPERATIONAL_HOURS = 'operational_hours';

    public const OPERATIONAL_NOTE = 'operational_note';

    public const SOCIAL_INSTAGRAM = 'social_instagram';

    public const SOCIAL_TIKTOK = 'social_tiktok';

    public const SOCIAL_X = 'social_x';

    /** Used until an admin saves the settings screen for the first time. */
    public const DEFAULTS = [
        self::WITHDRAWAL_MIN => 10000,
        self::WITHDRAWAL_MAX => 5000000,
        self::COMMISSION_PERCENT => 0,
        self::CS_WHATSAPP => '6281234567890',
        self::OFFICIAL_EMAIL => 'halo@warunghebat.id',
        self::OFFICE_ADDRESS => 'Jl. Tebet Raya No. 12, Jakarta Selatan',
        self::OPERATIONAL_DAYS => 'Senin–Sabtu',
        self::OPERATIONAL_HOURS => '07.00–22.00 WIB',
        self::OPERATIONAL_NOTE => 'Minggu & tanggal merah: slow response',
        self::SOCIAL_INSTAGRAM => '',
        self::SOCIAL_TIKTOK => '',
        self::SOCIAL_X => '',
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

    public static function csWhatsapp(): string
    {
        return static::asText(self::CS_WHATSAPP);
    }

    public static function officialEmail(): string
    {
        return static::asText(self::OFFICIAL_EMAIL);
    }

    public static function officeAddress(): string
    {
        return static::asText(self::OFFICE_ADDRESS);
    }

    public static function operationalDays(): string
    {
        return static::asText(self::OPERATIONAL_DAYS);
    }

    public static function operationalHours(): string
    {
        return static::asText(self::OPERATIONAL_HOURS);
    }

    public static function operationalNote(): string
    {
        return static::asText(self::OPERATIONAL_NOTE);
    }

    public static function socialInstagram(): string
    {
        return trim(static::asText(self::SOCIAL_INSTAGRAM, ''));
    }

    public static function socialTiktok(): string
    {
        return trim(static::asText(self::SOCIAL_TIKTOK, ''));
    }

    public static function socialX(): string
    {
        return trim(static::asText(self::SOCIAL_X, ''));
    }

    public static function socialWhatsapp(): string
    {
        return trim(static::waLink());
    }

    public static function waNumber(): string
    {
        $digits = preg_replace('/\D/', '', static::csWhatsapp());

        return $digits === '' ? (string) self::DEFAULTS[self::CS_WHATSAPP] : $digits;
    }

    public static function waLink(): string
    {
        return 'https://wa.me/'.static::waNumber();
    }

    public static function waDisplay(): string
    {
        $digits = static::waNumber();

        if ($digits === '') {
            return '';
        }

        if (str_starts_with($digits, '62')) {
            $local = substr($digits, 2);
            $groups = [substr($local, 0, 3), substr($local, 3, 4), substr($local, 7)];

            return '+62 '.implode('-', array_filter($groups));
        }

        if (str_starts_with($digits, '0')) {
            $local = substr($digits, 1);
            $groups = [substr($local, 0, 3), substr($local, 3, 4), substr($local, 7)];

            return '0'.implode('-', array_filter($groups));
        }

        return '+'.$digits;
    }

    /** @return array{instagram: string, tiktok: string, x: string, whatsapp: string, email: string} */
    public static function socialLinks(): array
    {
        return [
            'instagram' => static::socialInstagram(),
            'tiktok' => static::socialTiktok(),
            'x' => static::socialX(),
            'whatsapp' => static::waLink(),
            'email' => static::officialEmail(),
        ];
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
        // View composers read settings on every public page, so a missing
        // table (fresh checkout, tests without RefreshDatabase) falls back
        // to defaults instead of crashing the render. The fallback is never
        // cached, so real values load on the next call after migrating.
        try {
            return Cache::rememberForever(
                self::CACHE_KEY,
                fn (): array => static::query()->pluck('value', 'key')->all(),
            );
        } catch (QueryException) {
            return [];
        }
    }

    private static function asInt(string $key): int
    {
        $value = static::allValues()[$key] ?? null;

        return is_numeric($value) ? (int) $value : (int) self::DEFAULTS[$key];
    }

    private static function asText(string $key, string $fallback = ''): string
    {
        $values = static::allValues();

        if (array_key_exists($key, $values)) {
            return trim((string) $values[$key]);
        }

        return trim((string) (self::DEFAULTS[$key] ?? $fallback));
    }
}
