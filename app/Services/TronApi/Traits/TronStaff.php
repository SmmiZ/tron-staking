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
        $delegate = $this->transactionBuilder->delegateResource($trxAmount, $this->address['base58'], $receiverAddress, Resources::BANDWIDTH);

        return $this->signAndSendTransaction($delegate);
    }

    /**
     * @throws TronException
     */
    public function undelegateHotSpotBandwidth(string $userAddress, int $trxAmount): array
    {
        $undelegate = $this->transactionBuilder->undelegateResource($trxAmount, $this->address['base58'], $userAddress, Resources::BANDWIDTH);

        return $this->signAndSendTransaction($undelegate);
    }
}
