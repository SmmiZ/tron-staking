<?php

namespace App\Models;

use App\Enums\StakeStatuses;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\{Model, Relations\BelongsTo, SoftDeletes};

class Stake extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'amount',
        'days',
    ];

    protected $casts = [
        'amount' => 'int',
        'days' => 'int',
        'status' => StakeStatuses::class,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
