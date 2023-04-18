<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Consumer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'address',
        'resource_amount',
    ];

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
