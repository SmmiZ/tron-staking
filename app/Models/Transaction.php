<?php

namespace App\Models;

use App\Enums\TransactionTypes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'wallet_id',
        'from',
        'to',
        'trx_amount',
        'type',
        'tx_id',
    ];

    protected $casts = [
        'type' => TransactionTypes::class,
    ];
}
