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

    /**
     * @throws TronException
     */
    public function delegateHotSpotBandwidth(string $receiverAddress, int $trxAmount): array
    {
        $delegate = $this->transactionBuilder->delegateBandwidth($trxAmount, $this->address['base58'], $receiverAddress);

        return $this->signAndSendTransaction($delegate);
    }

    /**
     * @throws TronException
     */
    public function undelegateHotSpotBandwidth(string $receiverAddress, int $trxAmount): array
    {
        $undelegate = $this->transactionBuilder->undelegateResource($trxAmount, $receiverAddress, $this->address['base58'], Resources::BANDWIDTH);

        return $this->signAndSendTransaction($undelegate);
    }
}
