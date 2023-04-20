<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Wallet\StoreWalletRequest;
use App\Services\TronApi\Exception\TronException;
use App\Services\TronApi\Tron;
use App\Http\Resources\Wallet\{WalletCollection, WalletResource};
use App\Models\Wallet;
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

    public function update(StoreWalletRequest $request, Wallet $wallet): Response
    {
        return response([
            'status' => $wallet->update($request->validated()),
            'data' => (object)[],
        ]);
    }

    public function destroy(Wallet $wallet): Response
    {
        return response([
            'status' => $wallet->delete(),
        ]);
    }

    public function checkPermission(Request $request, Wallet $wallet): Response
    {
        $tron = new Tron();

        return response([
            'status' => $tron->checkPermissionOperations($wallet->address),
        ]);
    }
}
