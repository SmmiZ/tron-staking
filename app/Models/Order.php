<?php

namespace App\Models;

use App\Enums\{Resources, Statuses};
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\{Model, SoftDeletes};
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};

class Order extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'consumer_id',
        'resource_amount',
        'resource',
        'status',
    ];

    protected $casts = [
        'resource' => Resources::class,
        'status' => Statuses::class,
    ];

    public function consumer(): BelongsTo
    {
        return $this->belongsTo(Consumer::class);
    }

    public function executors(): HasMany
    {
        return $this->hasMany(OrderExecutor::class);
    }
}
