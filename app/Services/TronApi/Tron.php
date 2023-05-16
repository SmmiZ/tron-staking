<?php

declare(strict_types=1);

namespace App\Services\TronApi;

use App\Enums\TronTxTypes;
use App\Models\Wallet;
use App\Services\TronApi\Exception\TronException;
use App\Services\TronApi\Provider\{HttpProvider, HttpProviderInterface};
use App\Services\TronApi\Support\Secp;
use App\Services\TronApi\Traits\{TronAware, TronInfo};
use Elliptic\EC;

/**
 * A PHP API for interacting with the Tron (TRX)
 */
class Tron
{
    use TronAware;
    use TronInfo;

    public const ADDRESS_PREFIX = '41';
    public const ONE_SUN = 1000000;
    public const USDT_CONTRACT = 'TR7NHqjeKQxGTCi8q8ZY4pL8otSzgjLj6t';

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
    protected string $privateKey;

    /**
     * Transaction Builder
     *
     * @var TransactionBuilder
     */
    protected TransactionBuilder $transactionBuilder;

    /**
     * Provider manager
     *
     * @var TronManager
     */
    protected TronManager $manager;

    /**
     * Create a new Tron object
     *
     * @throws TronException
     */
    public function __construct(string $wallet = null, string $privateKey = null)
    {
        $fullNode = new HttpProvider(config('app.tron_net'));
        $this->setAddress($wallet ?? config('app.hot_spot_wallet'));
        $this->setPrivateKey($privateKey ?? config('app.hot_spot_private_key'));

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
        $this->address = [
            'hex' => $this->address2HexString($address),
            'base58' => $this->hexString2Address($address),
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
     * @param int $trxAmount
     * @return array
     * @throws TronException
     */
    public function unfreezeUserBalance(string $userAddress, int $trxAmount): array
    {
        $permissionId = $this->getPermissionId($userAddress);
        $resources = $this->getAccountResources($userAddress);

        if (!isset($resources['tronPowerLimit'])) {
            throw new TronException('No available TRX to unfreeze');
        }

        $unfreeze = $this->transactionBuilder->unfreezeEnergyBalance($trxAmount, $userAddress, $permissionId);

        return $this->signAndSendTransaction($unfreeze);
    }

    /**
     * Забрать на кошелек весь размороженный TRX
     *
     * @param string $userAddress
     * @return array
     * @throws TronException
     */
    public function withdrawDefrostedTrx(string $userAddress): array
    {
        $permissionId = $this->getPermissionId($userAddress);
        $withdraw = $this->transactionBuilder->withdrawExpireUnfreeze($userAddress, $permissionId);

        return $this->signAndSendTransaction($withdraw);
    }

    /**
     * Делегировать ресурс
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
     * Отозвать ранее делегированный ресурс
     *
     * @param string $ownerAddress
     * @param string $receiverAddress
     * @param int $trxAmount
     * @return array
     * @throws TronException
     */
    public function undelegateResource(string $ownerAddress, string $receiverAddress, int $trxAmount): array
    {
        $permissionId = $this->getPermissionId($ownerAddress);
        $undelegate = $this->transactionBuilder->undelegateResource($trxAmount, $ownerAddress, $receiverAddress, $permissionId);

        return $this->signAndSendTransaction($undelegate);
    }

    /**
     * Поиск разрешения на управление пользовательским аккаунтом
     *
     * @param string $address
     * @return mixed
     * @throws TronException
     */
    private function getPermissions(string $address): mixed
    {
        $accountPermissions = $this->getAccount($address)['active_permission'];

        foreach ($accountPermissions as $permission) {
            foreach ($permission['keys'] as $account) {
                if ($account['address'] == $this->address['hex']) {
                    return $permission;
                }
            }
        }

        throw new TronException('Cant find permission');
    }

    /**
     * Поиск ID разрешения для управления пользовательским аккаунтом
     *
     * @param string $address
     * @return mixed
     * @throws TronException
     */
    private function getPermissionId(string $address): int
    {
        $permission = $this->getPermissions($address);

        return $permission['id'];
    }

    /**
     * Проверить наличие доступа для управления
     *
     * @param string $address
     * @return bool
     * @throws TronException
     */
    public function hasAccess(string $address): bool
    {
        $permission = $this->getPermissions($address);
        $currentIndexes = $this->decodeHexadecimal($permission['operations']);

        return !array_diff(TronTxTypes::requiredIndexes(), $currentIndexes);
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
     * Забрать вознаграждение от голосования для пользователя
     *
     * @param string $ownerAddress
     * @return array
     * @throws TronException
     */
    public function rewardWithdraw(string $ownerAddress): array
    {
        $permissionId = $this->getPermissionId($ownerAddress);
        $withdraw = $this->transactionBuilder->rewardWithdraw($ownerAddress, $permissionId);

        return $this->signAndSendTransaction($withdraw);
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
     * Валидация адреса
     *
     * @param string|null $address
     * @return array
     * @throws TronException
     */
    public function validateAddress(string $address = null): array
    {
        $address = is_null($address) ? $this->address['hex'] : $this->toHex($address);

        return $this->manager->request('wallet/validateaddress', [
            'address' => $address,
        ]);
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
        $private = $ec->keyFromPrivate($key->priv);
        $pubKeyHex = $private->getPublic(false, 'hex');

        $pubKeyBin = hex2bin($pubKeyHex);
        $addressHex = $this->getAddressHex($pubKeyBin);
        $addressBin = hex2bin($addressHex);
        $addressBase58 = $this->getBase58CheckAddress($addressBin);

        return new TronAddress([
            'private_key' => $private->getPrivate('hex'),
            'public_key' => $pubKeyHex,
            'address_hex' => $addressHex,
            'address_base58' => $addressBase58,
        ]);
    }
}
