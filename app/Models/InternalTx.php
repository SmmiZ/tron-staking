<?php

namespace App\Models;

use App\Enums\InternalTxTypes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InternalTx extends Model
{
    use HasFactory;

    protected $fillable = [
        'amount',
        'type',
    ];

    protected $casts = [
        'type' => InternalTxTypes::class,
    ];
}
