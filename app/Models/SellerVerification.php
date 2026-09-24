<?php

namespace App\Models;

use Database\Factories\SellerVerificationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SellerVerification extends Model
{
    /** @use HasFactory<SellerVerificationFactory> */
    use HasFactory;

    public const STATUS_PENDING = 'pending';

    public const STATUS_VERIFIED = 'verified';

    public const STATUS_REJECTED = 'rejected';

    public const STATUSES = [self::STATUS_PENDING, self::STATUS_VERIFIED, self::STATUS_REJECTED];

    public const STATUS_LABELS = [
        self::STATUS_PENDING => 'Menunggu verifikasi',
        self::STATUS_VERIFIED => 'Terverifikasi',
        self::STATUS_REJECTED => 'Ditolak — perlu perbaikan',
    ];

    protected $fillable = [
        'user_id',
        'nik',
        'full_name',
        'ktp_path',
        'selfie_path',
        'storefront_path',
        'status',
        'rejection_reason',
        'verified_by',
        'verified_at',
    ];

    protected function casts(): array
    {
        return [
            'verified_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function statusLabel(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isVerified(): bool
    {
        return $this->status === self::STATUS_VERIFIED;
    }

    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    public function getKtpUrlAttribute(): ?string
    {
        if (! $this->ktp_path) {
            return null;
        }

        return asset('storage/'.ltrim($this->ktp_path, '/'));
    }

    public function getSelfieUrlAttribute(): ?string
    {
        if (! $this->selfie_path) {
            return null;
        }

        return asset('storage/'.ltrim($this->selfie_path, '/'));
    }

    public function getStorefrontUrlAttribute(): ?string
    {
        if (! $this->storefront_path) {
            return null;
        }

        return asset('storage/'.ltrim($this->storefront_path, '/'));
    }
}
