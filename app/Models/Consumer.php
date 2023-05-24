<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{HasMany, HasOne};
use Illuminate\Database\Eloquent\SoftDeletes;

class Consumer extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'address',
        'resource_amount',
    ];

    public function order(): HasOne
    {
        return $this->hasOne(Order::class);
    }

    public function resourceConsumptions(): HasMany
    {
        return $this->hasMany(ResourceConsumption::class);
    }
}
