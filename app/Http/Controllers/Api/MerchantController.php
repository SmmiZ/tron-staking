<?php

namespace App\Http\Controllers\Api;

use App\Models\Merchant;
use App\Http\Controllers\Controller;
use Illuminate\Http\{Request, Response};
use App\Services\TronApi\Tron;

class MerchantController extends Controller
{


    /**
     * 
     * Создаёт для оплаты новый кошелёк
     * 
     * @param Request $request
     * @return Response
     */
    public function tempAddressForTopUp(Request $request): Response
    {
        $merchant = Merchant::where('user_id', $request->user()->id)->where('created_at', '>', now()->subMinutes(30))->latest()->first();
        if (!$merchant) {
            $tron = new Tron();
            $newAddress = $tron->generateAddress();
            $merchant = Merchant::create([
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
                'timeleft' => $merchant->created_at->addHour()->diffInSeconds(now())
            ],
        ]);
    }
}
