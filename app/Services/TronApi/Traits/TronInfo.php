<?php

namespace App\Services\TronApi\Traits;

use App\Services\TronApi\Exception\TronException;
use App\Services\TronApi\Tron;
use App\Services\TronApi\Support\{Base58, Crypto, Hash, Keccak};

trait TronInfo
{
    /**
     * Query transaction based on id
     *
     * @param string $transactionID
     * @return array
     * @throws TronException
     */
    public function getTransaction(string $transactionID): array
    {
        $response = $this->manager->request('wallet/gettransactionbyid', [
            'value' => $transactionID,
        ]);

        if (!$response) {
            throw new TronException('Transaction not found');
        }

        return $response;
    }

    /**
     * Query information about an account
     *
     * @param string|null $address
     * @return array
     * @throws TronException
     */
    public function getAccount(string $address = null): array
    {
        $address = isset($address) ? $this->toHex($address) : $this->address['hex'];

        return $this->manager->request('wallet/getaccount', [
            'address' => $address,
        ]);
    }

    /**
     * Getting a balance
     *
     * @param string|null $address
     * @param bool $fromTron
     * @return float
     * @throws TronException
     */
    public function getBalance(string $address = null, bool $fromTron = false): float
    {
        $account = $this->getAccount($address);

        if (!array_key_exists('balance', $account)) {
            return 0;
        }

        return $fromTron ? $this->fromTron($account['balance']) : $account['balance'];
    }

    /**
     * Получить баланс в TRX
     *
     * @param string|null $address
     * @return float|int
     * @throws TronException
     */
    public function getTrxBalance(string $address = null): float|int
    {
        $account = $this->getAccount($address);

        if (!isset($account['balance'])) {
            return 0;
        }

        return $this->fromTron($account['balance']);
    }

    /**
     * Count all transactions on the network
     *
     * @return int
     * @throws TronException
     */
    public function getTransactionCount(): int
    {
        $response = $this->manager->request('wallet/totaltransaction');

        return $response['num'];
    }

    /**
     * Получить максимальный эквивалент TRX для делегирования ресурсов
     *
     * @return int TRX sun
     * @throws TronException
     */
    public function getCanDelegatedMaxSize(string $ownerAddress = null): int
    {
        $response = $this->transactionBuilder->getCanDelegatedMaxSize($ownerAddress ?? $this->address['hex']);

        return $response['max_size'] ?? 0;
    }

    /**
     * Получить кол-во доступной награды TRX
     *
     * @param string $address
     * @return float TRX
     * @throws TronException
     */
    public function getRewardAmount(string $address): float
    {
        $response = $this->manager->request('wallet/getReward', [
            'address' => $this->toHex($address),
        ]);

        return ($response['reward'] / Tron::ONE_SUN) ?? 0;
    }

    /**
     * Получить кол-во доступных заявок на разморозку
     *
     * @param string $ownerAddress
     * @return int|null
     * @throws TronException
     */
    public function getAvailableUnfreezeCount(string $ownerAddress): ?int
    {
        $response = $this->getManager()->request('wallet/getavailableunfreezecount', [
            'owner_address' => $this->toHex($ownerAddress),
        ]);

        return $response['count'] ?? null;
    }

    /**
     * Получить параметры текущей сети
     *
     * @return array
     * @throws TronException
     */
    public function getChainParameters(): array
    {
        $response = $this->getManager()->request('wallet/getchainparameters');

        return $response['chainParameter'] ?? [];
    }

    /**
     * Получить кол-во дней для разморозки TRX текущей сети
     *
     * @return int|null
     * @throws TronException
     */
    public function getChainUnfreezeDelayDays(): ?int
    {
        $params = $this->getChainParameters();
        $key = array_search('getUnfreezeDelayDays', array_column($params, 'key'));

        return $params[$key]['value'] ?? null;
    }

    /**
     * Получить список наблюдателей (SR)
     *
     * @return array
     * @throws TronException
     */
    public function listSuperRepresentatives(): array
    {
        return $this->manager->request('wallet/listwitnesses')['witnesses'];
    }

    /**
     * Выбор лучшего SR
     *
     * @throws TronException
     */
    public function getTopSrAddress(): string
    {
        $maxVoteCount = $srAddress = 0;
        foreach ($this->listSuperRepresentatives() as $sr) {
            if (isset($sr['voteCount'], $sr['address']) && $sr['voteCount'] > $maxVoteCount) {
                $maxVoteCount = $sr['voteCount'];
                $srAddress = $sr['address'];
            }
        }

        if ($srAddress == 0) {
            throw new TronException('No SR found');
        }

        return $srAddress;
    }

    /**
     * Query the resource information of the account
     *
     * @param string|null $address
     * @return array
     * @throws TronException
     */
    public function getAccountResources(string $address = null): array
    {
        $address = isset($address) ? $this->toHex($address) : $this->address['hex'];

        return $this->manager->request('/wallet/getaccountresource', [
            'address' => $address,
        ]);
    }

    public function getAddressHex(string $pubKeyBin): string
    {
        if (strlen($pubKeyBin) == 65) {
            $pubKeyBin = substr($pubKeyBin, 1);
        }

        $hash = Keccak::hash($pubKeyBin, 256);

        return self::ADDRESS_PREFIX . substr($hash, 24);
    }

    public function getBase58CheckAddress(string $addressBin): string
    {
        $hash0 = Hash::SHA256($addressBin);
        $hash1 = Hash::SHA256($hash0);
        $checksum = substr($hash1, 0, 4);
        $checksum = $addressBin . $checksum;

        return Base58::encode(Crypto::bin2bc($checksum));
    }

    /**
     * Получить информацию по транзакции
     *
     * @see https://developers.tron.network/reference/gettransactioninfobyid
     * @param string $transactionId
     * @return array
     * @throws TronException
     */
    public function getTransactionInfo(string $transactionId): array
    {
        return $this->manager->request('wallet/gettransactioninfobyid', [
            'value' => $transactionId,
        ]);
    }

    /**
     * Получить информацию о переданных\полученных ресурсах
     *
     * @see https://developers.tron.network/reference/getdelegatedresourceaccountindexv2-1
     * @param string $address
     * @return array
     * @throws TronException
     */
    public function getResourceRelations(string $address): array
    {
        return $this->manager->request('wallet/getdelegatedresourceaccountindexv2', [
            'value' => $this->toHex($address),
        ]);
    }

    /**
     * Получить подробную информацию о делегированных ресурсах
     *
     * @see https://developers.tron.network/reference/getdelegatedresourcev2
     * @param string $ownerAddress
     * @param string $receiverAddress
     * @return array
     * @throws TronException
     */
    public function getDelegatedResources(string $ownerAddress, string $receiverAddress): array
    {
        return $this->manager->request('wallet/getdelegatedresourcev2', [
            'fromAddress' => $this->toHex($ownerAddress),
            'toAddress' => $this->toHex($receiverAddress),
        ]);
    }

    /**
     * @todo не понятно, как работает и работает ли вообще
     * @see https://developers.tron.network/reference/getcanwithdrawunfreezeamount-1
     *
     * @param string $ownerAddress
     * @return array
     * @throws TronException
     */
    public function getCanWithdrawUnfreezeAmount(string $ownerAddress): array
    {
        return $this->manager->request('wallet/getcanwithdrawunfreezeamount', [
            'owner_address' => $this->toHex($ownerAddress),
            'timestamp' => now()->timestamp,
        ]);
    }

    /**
     * Получить информацию о контракте
     *
     * @see https://developers.tron.network/reference/getcontractinfo
     * @param string $contractAddress
     * @return array
     * @throws TronException
     */
    public function getContractInfo(string $contractAddress): array
    {
        return $this->manager->request('wallet/getcontractinfo', [
            'value' => $this->toHex($contractAddress),
        ]);
    }

    /**
     * Получить информацию об энергопотреблении контракта
     *
     * @see https://developers.tron.network/reference/triggerconstantcontract
     * @param string $contractAddress
     * @return array
     * @throws TronException
     */
    public function getContractEnergyConsumption(string $contractAddress): array
    {
        return $this->manager->request('wallet/triggerconstantcontract', [
            'owner_address' => $this->address['hex'],
            'contract_address' => $this->toHex($contractAddress),
            'function_selector' => 'transfer(address,uint256)',
            'parameter' => '000000000000000000000000a614f803b6fd780986a42c78ec9c7f77e6ded13c',
        ]);
    }
}
