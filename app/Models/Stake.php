<?php

namespace App\Models;

use App\Enums\Statuses;
use Illuminate\Database\Eloquent\{Model, SoftDeletes};
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasOneThrough};

class Stake extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'trx_amount',
    ];

    protected $casts = [
        'amount' => 'int',
        'status' => Statuses::class, //todo удалить если не более не актуально
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
