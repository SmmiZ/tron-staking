<?php

namespace App\Http\Controllers\Api;

use App\Enums\TronTxTypes;
use App\Http\Controllers\Controller;
use App\Services\TronApi\Tron;
use Illuminate\Http\Response;

class InfoController extends Controller
{
    /**
     * Информация для подключения пользовательского кошелька
     *
     * @return Response
     */
    public function connectInfo(): Response
    {
        $tron = new Tron();

        return response([
            'status' => true,
            'data' => [
                'address' => $tron->address['hex'],
                'operations' => $tron->encodeHexadecimal(TronTxTypes::requiredIndexes()),
                'weight' => 1,
            ],
        ]);
    }
}
