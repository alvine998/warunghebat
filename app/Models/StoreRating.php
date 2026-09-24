<?php

namespace App\Models;

use Database\Factories\StoreRatingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StoreRating extends Model
{
    /** @use HasFactory<StoreRatingFactory> */
    use HasFactory;

    protected $fillable = [
        'order_id',
        'user_id',
        'store_id',
        'rating',
        'comment',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
        ];
    }

    /** The completed order this rating belongs to (one rating per order). */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /** The buyer who left the rating. */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }
}
