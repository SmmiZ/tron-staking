<?php

namespace App\Models;

use App\Enums\Resources;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Consumer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'address',
        'resource',
        'amount',
    ];

    protected $casts = [
        'resource' => Resources::class,
    ];
}
