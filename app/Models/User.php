<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'phone', 'password', 'role', 'points', 'google_id'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /** Google accounts have no local password until they set one. */
    public function hasPassword(): bool
    {
        return $this->password !== null && $this->password !== '';
    }

    /** Where this user lands after signing in. */
    public function homeRoute(): string
    {
        return $this->isAdmin() ? route('admin.dashboard') : route('dashboard');
    }

    public function isSeller(): bool
    {
        return $this->role === 'penjual' || $this->role === 'admin';
    }

    /** Everyone can buy — penjual included. Role only gates selling. */
    public function canBuy(): bool
    {
        return in_array($this->role, ['pembeli', 'penjual', 'admin'], true);
    }

    public function canSell(): bool
    {
        return $this->isSeller();
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function inStoreTransactions(): HasMany
    {
        return $this->hasMany(InStoreTransaction::class, 'buyer_user_id');
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }

    /** Web-push device tokens for Firebase Cloud Messaging, one row per device. */
    public function fcmTokens(): HasMany
    {
        return $this->hasMany(FcmToken::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function store(): HasOne
    {
        return $this->hasOne(Store::class);
    }

    /** Bukti kepemilikan warung (KTP + selfie + foto warung), satu baris per penjual. */
    public function sellerVerification(): HasOne
    {
        return $this->hasOne(SellerVerification::class);
    }

    /** Status KYC: pending / verified / rejected, atau null bila belum mengajukan. */
    public function kycStatus(): ?string
    {
        if ($this->relationLoaded('sellerVerification') && $this->sellerVerification) {
            return $this->sellerVerification->status;
        }

        return $this->sellerVerification()->value('status');
    }

    /** Admin lolos dari kewajiban KYC; penjual wajib verified untuk jualan. */
    public function isKycVerified(): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        return $this->kycStatus() === SellerVerification::STATUS_VERIFIED;
    }

    /**
     * Product-derived stats for this seller's store.
     *
     * @return array{total: int, approved: int, pending: int, rejected: int, stock: int, inventory_value: int, low_stock: int, out_of_stock: int}
     */
    public function storeStats(): array
    {
        return [
            'total' => $this->products()->count(),
            'approved' => $this->products()->where('status', 'approved')->count(),
            'pending' => $this->products()->where('status', 'pending')->count(),
            'rejected' => $this->products()->where('status', 'rejected')->count(),
            'stock' => (int) $this->products()->sum('stock'),
            'inventory_value' => (int) $this->products()->selectRaw('COALESCE(SUM(price * stock), 0) AS total')->value('total'),
            'low_stock' => $this->products()->where('stock', '<=', 5)->where('stock', '>', 0)->count(),
            'out_of_stock' => $this->products()->where('stock', 0)->count(),
        ];
    }

    /** Products running low on stock, scarcest first. */
    public function lowStockProducts(int $limit = 5): Collection
    {
        return $this->products()->where('stock', '<=', 5)->orderBy('stock')->latest()->take($limit)->get();
    }

    /**
     * Inventory-based financial overview for this seller's catalog.
     *
     * @return array{inventory_value: int, avg_price: int, total: int, categories: Collection, top_products: Collection}
     */
    public function financialOverview(): array
    {
        return [
            'inventory_value' => (int) $this->products()->selectRaw('COALESCE(SUM(price * stock), 0) AS total')->value('total'),
            'avg_price' => (int) round($this->products()->avg('price') ?? 0),
            'total' => $this->products()->count(),
            'categories' => $this->products()
                ->selectRaw('category, COUNT(*) AS products, COALESCE(SUM(stock), 0) AS stock, COALESCE(SUM(price * stock), 0) AS value')
                ->groupBy('category')
                ->orderByDesc('value')
                ->get(),
            'top_products' => $this->products()
                ->selectRaw('products.*, (price * stock) AS stock_value')
                ->orderByDesc('stock_value')
                ->take(5)
                ->get(),
        ];
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
