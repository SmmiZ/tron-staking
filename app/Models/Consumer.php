<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{HasMany, HasOne};

class Consumer extends Model
{
    use HasFactory;

    protected $fillable = [
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
