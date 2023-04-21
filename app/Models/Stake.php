<?php

namespace App\Models;

use App\Enums\Statuses;
use Illuminate\Database\Eloquent\{Model, SoftDeletes};
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Stake extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'trx_amount',
    ];

    protected $casts = [
        'amount' => 'int',
        'status' => Statuses::class,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
