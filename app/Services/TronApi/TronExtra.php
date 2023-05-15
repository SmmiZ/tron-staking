<?php

namespace App\Services\TronApi;

use App\Services\TronApi\Exception\TronException;
use App\Services\TronApi\Support\{Base58Check, Hash};

class TronExtra
{
    public const ADDRESS_SIZE = 34;
    public const ADDRESS_PREFIX_BYTE = 0x41;

    /**
     * Set is object
     *
     * @param bool $value
     * @return Tron
     */
    public function setIsObject(bool $value): static
    {
        $this->isObject = boolval($value);

        return $this;
    }

    /**
     * Check Connection Providers
     *
     * @return array
     */
    public function isConnected(): array
    {
        return $this->manager->isConnected();
    }

    /**
     * Will return all events matching the filters.
     *
     * @param $contractAddress
     * @param int $sinceTimestamp
     * @param string|null $eventName
     * @param int $blockNumber
     * @return array
     * @throws TronException
     */
    public function getEventResult(
        $contractAddress,
        int $sinceTimestamp = 0,
        string $eventName = null,
        int $blockNumber = 0
    ) {
        if (!$this->isValidProvider($this->manager->eventServer())) {
            throw new TronException('No event server configured');
        }

        $routeParams = [];
        if ($eventName && !$contractAddress) {
            throw new TronException('Usage of event name filtering requires a contract address');
        }

        if ($blockNumber && !$eventName) {
            throw new TronException('Usage of block number filtering requires an event name');
        }

        if ($contractAddress) {
            $routeParams[] = $contractAddress;
        }
        if ($eventName) {
            $routeParams[] = $eventName;
        }
        if ($blockNumber) {
            $routeParams[] = $blockNumber;
        }

        $routeParams = implode('/', $routeParams);

        return $this->manager->request("event/contract/{$routeParams}?since={$sinceTimestamp}");
    }

    /**
     * Will return all events within a transactionID.
     *
     * @param string $transactionID
     * @return array
     * @throws TronException
     */
    public function getEventByTransactionID(string $transactionID): array
    {
        if (!$this->isValidProvider($this->manager->eventServer())) {
            throw new TronException('No event server configured');
        }

        return $this->manager->request("event/transaction/{$transactionID}");
    }

    /**
     * Total number of transactions in a block
     *
     * @param $block
     * @return int
     * @throws TronException
     */
    public function getBlockTransactionCount($block): int
    {
        $transaction = $this->getBlock($block)['transactions'];
        if (!$transaction) {
            return 0;
        }

        return count($transaction);
    }

    /**
     * Get transaction details from Block
     *
     * @param null $block
     * @param int $index
     * @return array | string
     * @throws TronException
     */
    public function getTransactionFromBlock($block = null, int $index = 0)
    {
        if (!is_integer($index) || $index < 0) {
            throw new TronException('Invalid transaction index provided');
        }

        $transactions = $this->getBlock($block)['transactions'];
        if (!$transactions || count($transactions) < $index) {
            throw new TronException('Transaction not found in block');
        }

        return $transactions[$index];
    }

    /**
     * Query the list of transactions received by an address
     *
     * @param string $address
     * @param int $limit
     * @param int $offset
     * @return array
     * @throws TronException
     */
    public function getTransactionsToAddress(string $address, int $limit = 30, int $offset = 0)
    {
        return $this->getTransactionsRelated($address, 'to', $limit, $offset);
    }

    /**
     * Query the list of transactions sent by an address
     *
     * @param string $address
     * @param int $limit
     * @param int $offset
     * @return array
     * @throws TronException
     */
    public function getTransactionsFromAddress(string $address, int $limit = 30, int $offset = 0)
    {
        return $this->getTransactionsRelated($address, 'from', $limit, $offset);
    }

    /**
     * Get token balance
     *
     * @param string $address
     * @param int $tokenId
     * @param bool $fromTron
     * @return array|int
     * @throws TronException
     */
    public function getTokenBalance(int $tokenId, string $address, bool $fromTron = false)
    {
        $account = $this->getAccount($address);

        if (isset($account['assetV2']) and !empty($account['assetV2'])) {
            $value = array_filter($account['assetV2'], function ($item) use ($tokenId) {
                return $item['key'] == $tokenId;
            });

            if (empty($value)) {
                throw new TronException('Token id not found');
            }

            $first = array_shift($value);

            return $fromTron ? $this->fromTron($first['value']) : $first['value'];
        }

        return 0;
    }

    /**
     * Query bandwidth information.
     *
     * @param string|null $address
     * @return array
     * @throws TronException
     */
    public function getBandwidth(string $address = null): array
    {
        $address = (!is_null($address) ? $this->toHex($address) : $this->address['hex']);

        return $this->manager->request('wallet/getaccountnet', [
            'address' => $address,
        ]);
    }

    /**
     * Getting data in the "from","to" directions
     *
     * @param string $address
     * @param string $direction
     * @param int $limit
     * @param int $offset
     * @return array
     * @throws TronException
     */
    public function getTransactionsRelated(string $address, string $direction = 'to', int $limit = 30, int $offset = 0): array
    {
        if (!in_array($direction, ['to', 'from'])) {
            throw new TronException('Invalid direction provided: Expected "to", "from"');
        }

        if (!is_integer($limit) || $limit < 0 || ($offset && $limit < 1)) {
            throw new TronException('Invalid limit provided');
        }

        if (!is_integer($offset) || $offset < 0) {
            throw new TronException('Invalid offset provided');
        }

        $response = $this->manager->request(sprintf('walletextension/gettransactions%sthis', $direction), [
            'account' => ['address' => $this->toHex($address)],
            'limit' => $limit,
            'offset' => $offset,
        ]);

        return array_merge($response, ['direction' => $direction]);
    }

    /**
     * Send token transaction to Blockchain
     *
     * @param string $to
     * @param float $amount
     * @param int|null $tokenID
     * @param string|null $from
     *
     * @return array
     * @throws TronException
     */
    public function sendTokenTransaction(string $to, float $amount, int $tokenID = null, string $from = null): array
    {
        if (is_null($from)) {
            $from = $this->address['hex'];
        }

        $transaction = $this->transactionBuilder->sendToken($to, $this->toTron($amount), (string)$tokenID, $from);

        return $this->signAndSendTransaction($transaction);
    }

    /**
     * Creating a new token based on Tron
     *
     * @param array token {
     *   "owner_address": "41e552f6487585c2b58bc2c9bb4492bc1f17132cd0",
     *   "name": "0x6173736574497373756531353330383934333132313538",
     *   "abbr": "0x6162627231353330383934333132313538",
     *   "total_supply": 4321,
     *   "trx_num": 1,
     *   "num": 1,
     *   "start_time": 1530894315158,
     *   "end_time": 1533894312158,
     *   "description": "007570646174654e616d6531353330363038383733343633",
     *   "url": "007570646174654e616d6531353330363038383733343633",
     *   "free_asset_net_limit": 10000,
     *   "public_free_asset_net_limit": 10000,
     *   "frozen_supply": { "frozen_amount": 1, "frozen_days": 2 }
     *
     * @return array
     * @throws TronException
     */
    public function createToken($token = [])
    {
        return $this->manager->request('wallet/createassetissue', [
            'owner_address' => $this->toHex($token['owner_address']),
            'name' => $this->stringUtf8toHex($token['name']),
            'abbr' => $this->stringUtf8toHex($token['abbr']),
            'description' => $this->stringUtf8toHex($token['description']),
            'url' => $this->stringUtf8toHex($token['url']),
            'total_supply' => $token['total_supply'],
            'trx_num' => $token['trx_num'],
            'num' => $token['num'],
            'start_time' => $token['start_time'],
            'end_time' => $token['end_time'],
            'free_asset_net_limit' => $token['free_asset_net_limit'],
            'public_free_asset_net_limit' => $token['public_free_asset_net_limit'],
            'frozen_supply' => $token['frozen_supply'],
        ]);
    }

    /**
     * Transfer Token
     *
     * @param string $to
     * @param int $amount
     * @param string $tokenID
     * @param string|null $from
     * @return array
     * @throws TronException
     */
    public function sendToken(string $to, int $amount, string $tokenID, string $from = null): array
    {
        if ($from == null) {
            $from = $this->address['hex'];
        }

        $transfer = $this->transactionBuilder->sendToken($to, $amount, $tokenID, $from);

        return $this->signAndSendTransaction($transfer);
    }

    /**
     * Purchase a Token
     *
     * @param $issuerAddress
     * @param $tokenID
     * @param $amount
     * @param null $buyer
     * @return array
     * @throws TronException
     */
    public function purchaseToken($issuerAddress, $tokenID, $amount, $buyer = null): array
    {
        if ($buyer == null) {
            $buyer = $this->address['hex'];
        }

        $purchase = $this->transactionBuilder->purchaseToken($issuerAddress, $tokenID, $amount, $buyer);

        return $this->signAndSendTransaction($purchase);
    }

    /**
     * Update a Token's information
     *
     * @param string $description
     * @param string $url
     * @param int $freeBandwidth
     * @param int $freeBandwidthLimit
     * @param string|null $owner_address
     * @return array
     * @throws TronException
     */
    public function updateToken(
        string $description,
        string $url,
        int $freeBandwidth = 0,
        int $freeBandwidthLimit = 0,
        string $owner_address = null
    ) {
        if ($owner_address == null) {
            $owner_address = $this->address['hex'];
        }

        $withdraw = $this->transactionBuilder->updateToken(
            $description,
            $url,
            $freeBandwidth,
            $freeBandwidthLimit,
            $owner_address
        );

        return $this->signAndSendTransaction($withdraw);
    }

    /**
     * Node list
     *
     * @return array
     * @throws TronException
     */
    public function listNodes(): array
    {
        $nodes = $this->manager->request('wallet/listnodes');

        return array_map(function ($item) {
            $address = $item['address'];

            return sprintf('%s:%s', $this->toUtf8($address['host']), $address['port']);
        }, $nodes['nodes']);
    }

    /**
     * List the tokens issued by an account.
     *
     * @param string|null $address
     * @return array
     * @throws TronException
     */
    public function getTokensIssuedByAddress(string $address = null): array
    {
        $address = (!is_null($address) ? $this->toHex($address) : $this->address['hex']);

        return $this->manager->request('wallet/getassetissuebyaccount', [
            'address' => $address,
        ]);
    }

    /**
     * Query token by name.
     *
     * @param $tokenID
     * @return array
     * @throws TronException
     */
    public function getTokenFromID($tokenID = null): array
    {
        return $this->manager->request('wallet/getassetissuebyname', [
            'value' => $this->stringUtf8toHex($tokenID),
        ]);
    }

    /**
     * Query a range of blocks by block height
     *
     * @param int $start
     * @param int $end
     * @return array
     * @throws TronException
     */
    public function getBlockRange(int $start = 0, int $end = 30): array
    {
        if (!is_integer($start) || $start < 0) {
            throw new TronException('Invalid start of range provided');
        }

        if (!is_integer($end) || $end <= $start) {
            throw new TronException('Invalid end of range provided');
        }

        return $this->manager->request('wallet/getblockbylimitnext', [
            'startNum' => intval($start),
            'endNum' => intval($end) + 1,
        ])['block'];
    }

    /**
     * Query the list of Tokens with pagination
     *
     * @param int $limit
     * @param int $offset
     * @return array
     * @throws TronException
     */
    public function listTokens(int $limit = 0, int $offset = 0): array
    {
        if (!is_integer($limit) || $limit < 0 || ($offset && $limit < 1)) {
            throw new TronException('Invalid limit provided');
        }

        if (!is_integer($offset) || $offset < 0) {
            throw new TronException('Invalid offset provided');
        }

        if (!$limit) {
            return $this->manager->request('wallet/getassetissuelist')['assetIssue'];
        }

        return $this->manager->request('wallet/getpaginatedassetissuelist', [
            'offset' => intval($offset),
            'limit' => intval($limit),
        ])['assetIssue'];
    }

    /**
     * Get the time of the next Super Representative vote
     *
     * @return float
     * @throws TronException
     */
    public function timeUntilNextVoteCycle(): float
    {
        $num = $this->manager->request('wallet/getnextmaintenancetime')['num'];

        if ($num == -1) {
            throw new TronException('Failed to get time until next vote cycle');
        }

        return floor($num / 1000);
    }
    /**
     * Validate Tron Address (Locale)
     *
     * @param string|null $address
     * @return bool
     */
    public function isAddress(string $address = null): bool
    {
        if (strlen($address) !== self::ADDRESS_SIZE) {
            return false;
        }

        $address = Base58Check::decode($address, 0, 0, false);
        $utf8 = hex2bin($address);

        if (strlen($utf8) !== 25) {
            return false;
        }
        if (!str_starts_with($utf8, chr(self::ADDRESS_PREFIX_BYTE))) {
            return false;
        }

        $checkSum = substr($utf8, 21);
        $address = substr($utf8, 0, 21);

        $hash0 = Hash::SHA256($address);
        $hash1 = Hash::SHA256($hash0);
        $checkSum1 = substr($hash1, 0, 4);

        if ($checkSum === $checkSum1) {
            return true;
        }

        return false;
    }

    /**
     * Deploys a contract
     *
     * @param $abi
     * @param $bytecode
     * @param $feeLimit
     * @param $address
     * @param int $callValue
     * @param int $bandwidthLimit
     * @return array
     * @throws TronException
     */
    public function deployContract($abi, $bytecode, $feeLimit, $address, $callValue = 0, $bandwidthLimit = 0)
    {
        $payable = array_filter(json_decode($abi, true), function ($v) {
            if ($v['type'] == 'constructor' && $v['payable']) {
                return $v['payable'];
            }

            return null;
        });

        if ($feeLimit > 1000000000) {
            throw new TronException('fee_limit must not be greater than 1000000000');
        }

        if ($payable && $callValue == 0) {
            throw new TronException('call_value must be greater than 0 if contract is type payable');
        }

        if (!$payable && $callValue > 0) {
            throw new TronException('call_value can only equal to 0 if contract type isn‘t payable');
        }

        return $this->manager->request('wallet/deploycontract', [
            'owner_address' => $this->toHex($address),
            'fee_limit' => $feeLimit,
            'call_value' => $callValue,
            'consume_user_resource_percent' => $bandwidthLimit,
            'abi' => $abi,
            'bytecode' => $bytecode,
        ]);
    }


    /**
     * Get a list of exchanges
     *
     * @return array
     * @throws TronException
     */
    public function listExchanges(): array
    {
        return $this->manager->request('/wallet/listexchanges');
    }

    /**
     * Create a new account
     *
     * @return TronAddress
     * @throws TronException
     */
    public function createAccount(): TronAddress
    {
        return $this->generateAddress();
    }

    /**
     * Helper function that will convert HEX to UTF8
     *
     * @param $str
     * @return string
     */
    public function toUtf8($str): string
    {
        return pack('H*', $str);
    }

    /**
     * Query token by id.
     *
     * @param string $token_id
     * @return array
     * @throws TronException
     */
    public function getTokenByID(string $token_id): array
    {
        return $this->manager->request('/wallet/getassetissuebyid', [
            'value' => $token_id,
        ]);
    }
}
