<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'address',
        'subscribe_address',
        'subscribe_id',
        'private_key',
        'balance',
        'token_balance',
        'last_transaction_time',
        'stake_timestamp',
    ];
}
