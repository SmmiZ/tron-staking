<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MerchantWallet;
use App\Services\TronApi\Exception\TronException;
use App\Services\TronApi\Tron;
use Illuminate\Http\{Request, Response};

class MerchantWalletController extends Controller
{
    /**
     * Получить временный кошелёк для оплаты
     *
     * @param Request $request
     * @return Response
     * @throws TronException
     */
    public function getTempAddress(Request $request): Response
    {
        $merchant = MerchantWallet::where('user_id', $request->user()->id)->where('created_at', '>', now()->subMinutes(30))->latest()->first();

        if (!$merchant) {
            $tron = new Tron();
            $newAddress = $tron->generateAddress();
            $merchant = MerchantWallet::create([
                'user_id' => $request->user()->id,
                'address' => $newAddress->getAddress(true),
                'hex_address' => $newAddress->getAddress(),
                'private_key' => $newAddress->getPrivateKey(),
            ]);
        }

        return response([
            'status' => true,
            'data' => [
                'address' => $merchant->address,
                'time_left' => $merchant->created_at->addHour()->diffInSeconds(now())
            ],
        ]);
    }
}
