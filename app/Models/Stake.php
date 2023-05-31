<?php

namespace App\Models;

use Illuminate\Database\Eloquent\{Model, SoftDeletes};
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasOneThrough};

class Stake extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'trx_amount',
        'available_at',
        'is_closes',
    ];

    protected $casts = [
        'amount' => 'int',
        'available_at' => 'datetime',
        'is_closes' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function wallet(): HasOneThrough
    {
        return $this->hasOneThrough(Wallet::class, User::class, 'id', 'user_id', 'user_id');
    }
}
