<?php

declare(strict_types=1);

namespace App\Services\TronApi;

use App\Models\Wallet;
use App\Services\TronApi\Concerns\{ManagesTronscan, ManagesUniversal};
use App\Services\TronApi\Exception\TronException;
use App\Services\TronApi\Provider\{HttpProvider, HttpProviderInterface};
use App\Services\TronApi\Support\{Base58, Base58Check, Crypto, Hash, Keccak, Secp, Utils};
use Elliptic\EC;

/**
 * A PHP API for interacting with the Tron (TRX)
 */
class Tron implements TronInterface
{
    use ManagesTronscan;
    use ManagesUniversal;
    use TronAwareTrait;

    public const ADDRESS_SIZE = 34;
    public const ADDRESS_PREFIX = '41';
    public const ADDRESS_PREFIX_BYTE = 0x41;
    public const ONE_SUN = 1000000;

    /**
     * Default Address:
     * Example:
     *      - base58:   T****
     *      - hex:      41****
     *
     * @var array
     */
    public array $address = [
        'base58' => null,
        'hex' => null,
    ];

    /**
     * Private key
     *
     * @var string
     */
    protected $privateKey;

    /**
     * Default block
     *
     * @var string|integer|bool
     */
    protected $defaultBlock = 'latest';

    /**
     * Transaction Builder
     *
     * @var TransactionBuilder
     */
    protected TransactionBuilder $transactionBuilder;

    /**
     * Transaction Builder
     */
    protected TransactionBuilder $trc20Contract;

    /**
     * Provider manager
     *
     * @var TronManager
     */
    protected TronManager $manager;

    /**
     * Object Result
     *
     * @var bool
     */
    protected bool $isObject = false;

    /**
     * Create a new Tron object
     *
     * @throws TronException
     */
    public function __construct()
    {
        $fullNode = new HttpProvider(config('app.tron_net'));
        $this->setAddress(config('app.hot_spot_wallet'));
        $this->setPrivateKey(config('app.hot_spot_private_key'));

        //todo не ясно - зачем остальные параметры, кроме fullNode. Вырезать?
        $this->setManager(
            new TronManager($this, [
                'fullNode' => $fullNode,
                'solidityNode' => $fullNode,
                'eventServer' => $fullNode,
            ])
        );

        $this->transactionBuilder = new TransactionBuilder($this);
    }

    /**
     * Enter the link to the manager nodes
     *
     * @param $providers
     */
    public function setManager($providers): void
    {
        $this->manager = $providers;
    }

    /**
     * Get provider manager
     *
     * @return TronManager
     */
    public function getManager(): TronManager
    {
        return $this->manager;
    }

    /**
     * Contract module
     *
     * @param string $contractAddress
     * @param string|null $abi
     * @return TRC20Contract
     */
    public function contract(string $contractAddress, string $abi = null): TRC20Contract
    {
        return new TRC20Contract($this, $contractAddress, $abi);
    }

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
     * Get Transaction Builder
     *
     * @return TransactionBuilder
     */
    public function getTransactionBuilder(): TransactionBuilder
    {
        return $this->transactionBuilder;
    }

    /**
     * Check connected provider
     *
     * @param $provider
     * @return bool
     */
    public function isValidProvider($provider): bool
    {
        return ($provider instanceof HttpProviderInterface);
    }

    /**
     * Enter the default block
     *
     * @param bool $blockID
     * @return void
     * @throws TronException
     */
    public function setDefaultBlock(bool $blockID = false): void
    {
        if ($blockID === false || $blockID == 'latest' || $blockID == 'earliest' || $blockID === 0) {
            $this->defaultBlock = $blockID;

            return;
        }

        if (!is_integer($blockID)) {
            throw new TronException('Invalid block ID provided');
        }

        $this->defaultBlock = abs($blockID);
    }

    /**
     * Get default block
     *
     * @return string|integer|bool
     */
    public function getDefaultBlock()
    {
        return $this->defaultBlock;
    }

    /**
     * Enter your private account key
     *
     * @param string $privateKey
     */
    private function setPrivateKey(string $privateKey): void
    {
        $this->privateKey = $privateKey;
    }

    /**
     * Enter your account address
     *
     * @param string $address
     */
    public function setAddress(string $address): void
    {
        $_toHex = $this->address2HexString($address);
        $_fromHex = $this->hexString2Address($address);

        $this->address = [
            'hex' => $_toHex,
            'base58' => $_fromHex,
        ];
    }

    /**
     * Get account address
     *
     * @return array
     */
    public function getAddress(): array
    {
        return $this->address;
    }

    /**
     * Get customized provider data
     *
     * @return array
     */
    public function providers(): array
    {
        return $this->manager->getProviders();
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
     * Last block number
     *
     * @return array
     * @throws TronException
     */
    public function getCurrentBlock(): array
    {
        return $this->manager->request('wallet/getnowblock');
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
     * Get block details using HashString or blockNumber
     *
     * @param null $block
     * @return array
     * @throws TronException
     */
    public function getBlock($block = null): array
    {
        $block = (is_null($block) ? $this->defaultBlock : $block);

        if ($block === false) {
            throw new TronException('No block identifier provided');
        }

        if ($block == 'earliest') {
            $block = 0;
        }

        if ($block == 'latest') {
            return $this->getCurrentBlock();
        }

        if (Utils::isHex($block)) {
            return $this->getBlockByHash($block);
        }

        return $this->getBlockByNumber($block);
    }

    /**
     * Query block by ID
     *
     * @param string $hashBlock
     * @return array
     * @throws TronException
     */
    public function getBlockByHash(string $hashBlock): array
    {
        return $this->manager->request('wallet/getblockbyid', [
            'value' => $hashBlock,
        ]);
    }

    /**
     * Query block by height
     *
     * @param int $blockID
     * @return array
     * @throws TronException
     */
    public function getBlockByNumber(int $blockID): array
    {
        if ($blockID < 0) {
            throw new TronException('Invalid block number provided');
        }

        $response = $this->manager->request('wallet/getblockbynum', [
            'num' => $blockID,
        ]);

        if (empty($response)) {
            throw new TronException('Block not found');
        }

        return $response;
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
     * Query transaction fee based on id
     *
     * @param string $transactionID
     * @return array
     * @throws TronException
     */
    public function getTransactionInfo(string $transactionID): array
    {
        return $this->manager->request('walletsolidity/gettransactioninfobyid', [
            'value' => $transactionID,
        ]);
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
     * Send transaction to Blockchain
     *
     * @param string $to
     * @param float $amount
     * @param string|null $message
     * @param string|null $from
     *
     * @return array
     * @throws TronException
     */
    public function sendTrx(string $to, float $amount, string $from = null, string $message = null): array
    {
        if (is_null($from)) {
            $from = $this->address['hex'];
        }

        $transaction = $this->transactionBuilder->sendTrx($to, $amount, $from, $message);

        return $this->signAndSendTransaction($transaction);
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
     * Sign the transaction, the api has the risk of leaking the private key,
     * please make sure to call the api in a secure environment
     *
     * @param $transaction
     * @param string|null $message
     * @return array
     * @throws TronException
     */
    public function signTransaction($transaction, string $message = null): array
    {
        if (!$this->privateKey) {
            throw new TronException('Missing private key');
        }

        if (!is_array($transaction)) {
            throw new TronException('Invalid transaction provided');
        }

        if (isset($transaction['Error'])) {
            throw new TronException($transaction['Error']);
        }

        if (isset($transaction['signature'])) {
            throw new TronException('Transaction is already signed');
        }

        if (!is_null($message)) {
            $transaction['raw_data']['data'] = $this->stringUtf8toHex($message);
        }

        $signature = Secp::sign($transaction['txID'], $this->privateKey);
        $transaction['signature'] = [$signature];

        return $transaction;
    }

    /**
     * Broadcast the signed transaction
     *
     * @param $signedTransaction
     * @return array
     * @throws TronException
     */
    public function sendRawTransaction($signedTransaction): array
    {
        if (!is_array($signedTransaction)) {
            throw new TronException('Invalid transaction provided');
        }

        if (!array_key_exists('signature', $signedTransaction) || !is_array($signedTransaction['signature'])) {
            throw new TronException('Transaction is not signed');
        }

        return $this->manager->request('wallet/broadcasttransaction', $signedTransaction);
    }

    /**
     * Modify account name
     * Note: Username is allowed to edit only once.
     *
     * @param string|null $address
     * @param string $accountName
     * @return array
     * @throws TronException
     */
    public function changeAccountName(string $accountName, string $address = null): array
    {
        $address = (!is_null($address) ? $address : $this->address['hex']);

        $transaction = $this->manager->request('wallet/updateaccount', [
            'account_name' => $this->stringUtf8toHex($accountName),
            'owner_address' => $this->toHex($address),
        ]);
        $signedTransaction = $this->signTransaction($transaction);

        return $this->sendRawTransaction($signedTransaction);
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
     * Create an account.
     * Uses an already activated account to create a new account
     *
     * @param string $address
     * @param string $newAccountAddress
     * @return array
     * @throws TronException
     */
    public function registerAccount(string $address, string $newAccountAddress): array
    {
        return $this->manager->request('wallet/createaccount', [
            'owner_address' => $this->toHex($address),
            'account_address' => $this->toHex($newAccountAddress),
        ]);
    }

    /**
     * Apply to become a super representative
     *
     * @param string $address
     * @param string $url
     * @return array
     * @throws TronException
     */
    public function applyForSuperRepresentative(string $address, string $url): array
    {
        return $this->manager->request('wallet/createwitness', [
            'owner_address' => $this->toHex($address),
            'url' => $this->stringUtf8toHex($url),
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
     * Заморозить TRX клиента
     *
     * @param Wallet $wallet
     * @param int $trxAmount
     * @return array
     * @throws TronException
     */
    public function freezeUserBalance(Wallet $wallet, int $trxAmount): array
    {
        $permissionId = $this->getPermissionId($wallet->address);
        $freeze = $this->transactionBuilder->freezeBalance2Energy($trxAmount, $wallet->address, $permissionId);

        return $this->signAndSendTransaction($freeze);
    }

    /**
     * Разморозить TRX клиента
     *
     * @param string $userAddress
     * @return array
     * @throws TronException
     */
    public function unfreezeUserBalance(string $userAddress): array
    {
        $permissionId = $this->getPermissionId($userAddress);
        $resources = $this->getAccountResources($userAddress);

        if (!isset($resources['tronPowerLimit'])) {
            throw new TronException('No available TRX to unfreeze');
        }

        $sunAmount = $resources['tronPowerLimit'] * 1000000;
        $unfreeze = $this->transactionBuilder->unfreezeEnergyBalance($sunAmount, $userAddress, $permissionId);

        return $this->signAndSendTransaction($unfreeze);
    }

    /**
     * Передать ресурс с одного кошелька на другой
     *
     * @param string $ownerAddress
     * @param string $receiverAddress
     * @param int $trxAmount
     * @return array
     * @throws TronException
     */
    public function delegateResource(string $ownerAddress, string $receiverAddress, int $trxAmount): array
    {
        $permissionId = $this->getPermissionId($ownerAddress);
        $delegate = $this->transactionBuilder->delegateResource($trxAmount, $ownerAddress, $receiverAddress, $permissionId);

        return $this->signAndSendTransaction($delegate);
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
     * Поиск ID разрешения для управления доверенным аккаунтом
     *
     * @param string $address
     * @return int
     * @throws TronException
     */
    private function getPermissionId(string $address): int
    {
        $accountPermissions = $this->getAccount($address)['active_permission'];

        $permissionId = null;
        foreach ($accountPermissions as $permission) {
            foreach ($permission['keys'] as $account) {
                if ($account['address'] == $this->address['hex']) {
                    $permissionId = $permission['id'];
                    break;
                }
            }
        }

        if (!isset($permissionId)) {
            throw new TronException('Cant find permission id');
        }

        return $permissionId;
    }

    /**
     * Подписать и отправить транзакцию
     *
     * @param $transaction
     * @return array
     * @throws TronException
     */
    private function signAndSendTransaction($transaction): array
    {
        $signedTransaction = $this->signTransaction($transaction);
        $response = $this->sendRawTransaction($signedTransaction);

        return array_merge($response, $signedTransaction);
    }

    /**
     * Withdraw Super Representative rewards, useable every 24 hours.
     *
     * @param string|null $ownerAddress
     * @return array
     * @throws TronException
     */
    public function withdrawBlockRewards(string $ownerAddress = null): array
    {
        //todo owner address?
        if ($ownerAddress == null) {
            $ownerAddress = $this->address['hex'];
        }

        $withdraw = $this->transactionBuilder->withdrawBlockRewards($ownerAddress);

        return $this->signAndSendTransaction($withdraw);
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
     * Query the latest blocks
     *
     * @param int $limit
     * @return array
     * @throws TronException
     */
    public function getLatestBlocks(int $limit = 1): array
    {
        if (!is_integer($limit) || $limit <= 0) {
            throw new TronException('Invalid limit provided');
        }

        return $this->manager->request('wallet/getblockbylatestnum', [
            'num' => $limit,
        ])['block'];
    }

    /**
     * Проголосовать за наблюдателя (SR)
     *
     * @param string $witnessAddress
     * @param Wallet|null $wallet
     * @return array
     * @throws TronException
     */
    public function voteWitness(string $witnessAddress, Wallet $wallet = null): array
    {
        $ownerAddress = isset($wallet) ? $wallet->address : $this->address['base58'];
        $resources = $this->getAccountResources($ownerAddress);

        if (!isset($resources['tronPowerLimit']) || $resources['tronPowerLimit'] <= 0) {
            throw new TronException('No available votes');
        }

        $availableVotes = $resources['tronPowerLimit'] - ($resources['tronPowerUsed'] ?? 0);
        $permissionId = $this->getPermissionId($ownerAddress);

        $vote = $this->transactionBuilder->voteWitness($ownerAddress, $witnessAddress, $availableVotes, $permissionId);

        return $this->signAndSendTransaction($vote);
    }

    /**
     * Query the list of Super Representatives
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
     * Validate address
     *
     * @param string|null $address
     * @param bool $hex
     * @return array
     * @throws TronException
     */
    public function validateAddress(string $address = null, bool $hex = false): array
    {
        $address = (!is_null($address) ? $address : $this->address['hex']);
        if ($hex) {
            $address = $this->toHex($address);
        }

        return $this->manager->request('wallet/validateaddress', [
            'address' => $address,
        ]);
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
     * Generate new address
     *
     * @return TronAddress
     * @throws TronException
     */
    public function generateAddress(): TronAddress
    {
        $ec = new EC('secp256k1');

        // Generate keys
        $key = $ec->genKeyPair();
        $priv = $ec->keyFromPrivate($key->priv);
        $pubKeyHex = $priv->getPublic(false, "hex");

        $pubKeyBin = hex2bin($pubKeyHex);
        $addressHex = $this->getAddressHex($pubKeyBin);
        $addressBin = hex2bin($addressHex);
        $addressBase58 = $this->getBase58CheckAddress($addressBin);

        return new TronAddress([
            'private_key' => $priv->getPrivate('hex'),
            'public_key' => $pubKeyHex,
            'address_hex' => $addressHex,
            'address_base58' => $addressBase58,
        ]);
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
