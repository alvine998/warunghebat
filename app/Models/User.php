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

#[Fillable(['name', 'email', 'password', 'role', 'points'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
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

    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function store(): HasOne
    {
        return $this->hasOne(Store::class);
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
