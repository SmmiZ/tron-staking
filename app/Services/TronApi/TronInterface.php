<?php declare(strict_types=1);

namespace App\Services\TronApi;

use App\Services\TronApi\Exception\TronException;

interface TronInterface
{
    /**
     * Enter the link to the manager nodes
     *
     * @param $providers
     */
    public function setManager($providers);

    /**
     * Enter your account address
     *
     * @param string $address
     */
    public function setAddress(string $address);

    /**
     * Getting a balance
     *
     * @param string|null $address
     */
    public function getBalance(string $address = null);

    /**
     * Query transaction based on id
     *
     * @param string $transactionID
     */
    public function getTransaction(string $transactionID);

    /**
     * Count all transactions on the network
     **/
    public function getTransactionCount();

    /**
     * Send TRX
     *
     * @param string $to
     * @param float $amount
     * @param string|null $from
     *
     * @throws TronException
     */
    public function sendTrx(string $to, float $amount, string $from = null);

    /**
     * Modify account name
     * Note: Username is allowed to edit only once.
     *
     * @param string $accountName
     * @param string|null $address
     */
    public function changeAccountName(string $accountName, string $address = null);

    /**
     * Create an account.
     * Uses an already activated account to create a new account
     *
     * @param string $address
     * @param string $newAccountAddress
     */
    public function registerAccount(string $address, string $newAccountAddress);

    /**
     * Apply to become a super representative
     *
     * @param string $address
     * @param string $url
     */
    public function applyForSuperRepresentative(string $address, string $url);


    /**
     * Get block details using HashString or blockNumber
     *
     * @param null $block
     */
    public function getBlock($block = null);

    /**
     * Query the latest blocks
     *
     * @param int $limit
     */
    public function getLatestBlocks(int $limit = 1);

    /**
     * Validate Address
     *
     * @param string $address
     * @param bool $hex
     */
    public function validateAddress(string $address, bool $hex = false);

    /**
     * Generate new address
     */
    public function generateAddress();

    /**
     * Check the address before converting to Hex
     *
     * @param $sHexAddress
     */
    public function address2HexString($sHexAddress);
}
