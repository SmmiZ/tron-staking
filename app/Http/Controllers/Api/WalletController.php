<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Wallet\StoreWalletRequest;
use App\Http\Resources\Wallet\{WalletCollection, WalletResource};
use App\Models\Wallet;
use App\Services\TronApi\Exception\TronException;
use App\Services\TronApi\Tron;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\{Request, Response};

class WalletController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Wallet::class);
    }

    public function index(Request $request): WalletCollection
    {
        return new WalletCollection($request->user()->wallets);
    }

    public function store(StoreWalletRequest $request): Response
    {
        $wallet = $request->user()->wallets()->create($request->validated());

        return response([
            'status' => true,
            'data' => [
                'id' => $wallet->id,
            ],
        ]);
    }

    public function show(Wallet $wallet): WalletResource
    {
        return new WalletResource($wallet);
    }

    public function destroy(Wallet $wallet): Response
    {
        return response([
            'status' => $wallet->delete(),
        ]);
    }

    /**
     * @throws TronException|AuthorizationException
     */
    public function checkAccess(Request $request, Wallet $wallet): Response
    {
        $this->authorize('checkAccess', $wallet);

        return response([
            'status' => (new Tron())->hasAccess($wallet->address),
        ]);
    }
}
