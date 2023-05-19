<?php

namespace App\Services\TronApi\Traits;

use App\Enums\Resources;
use App\Services\TronApi\Exception\TronException;

trait TronStaff
{
    /**
     * @throws TronException
     */
    public function freezeHotSpotBalance(int $trxAmount): array
    {
        $freeze = $this->transactionBuilder->freezeBalance($trxAmount, $this->address['base58'], Resources::BANDWIDTH);

        return $this->signAndSendTransaction($freeze);
    }
}
