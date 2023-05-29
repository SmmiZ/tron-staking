<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\{Model, SoftDeletes};
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasOneThrough};

class OrderExecutor extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'order_id',
        'user_id',
        'trx_amount',
        'resource_amount',
        'unlocked_at',
        'deleted_at'
    ];

    protected $casts = [
        'unlocked_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function wallet(): HasOneThrough
    {
        return $this->hasOneThrough(Wallet::class, User::class, 'id', 'user_id', 'user_id');
    }
}
