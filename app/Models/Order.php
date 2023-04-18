<?php

namespace App\Models;

use App\Enums\{Resources, Statuses};
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'consumer_id',
        'resource_amount',
        'resource',
        'status',
        'executed_at',
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
