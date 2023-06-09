<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Transaction\{InternalTransactionCollection, TronTransactionCollection};
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function internalTxs(Request $request): InternalTransactionCollection
    {
        return new InternalTransactionCollection($request->user()->internalTxs()->paginate(20));
    }

    public function tronTxs(Request $request): TronTransactionCollection
    {
        $user = $request->user()->load('wallets:user_id,address');

        return new TronTransactionCollection($user->tronTxs()->paginate(20));
    }
}
