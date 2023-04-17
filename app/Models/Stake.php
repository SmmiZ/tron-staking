<?php

namespace App\Models;

use App\Enums\Statuses;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\{Model, Relations\BelongsTo};

class Stake extends Model
{
    use HasFactory;

    protected $fillable = [
        'amount',
        'unstake_at',
    ];

    protected $casts = [
        'amount' => 'int',
        'status' => Statuses::class,
        'unstake_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
