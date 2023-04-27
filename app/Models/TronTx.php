<?php

namespace App\Models;

use App\Enums\Operations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TronTx extends Model
{
    use HasFactory;

    protected $fillable = [
        'from',
        'to',
        'trx_amount',
        'type',
        'tx_id',
    ];

    protected $casts = [
        'type' => Operations::class,
    ];
}
