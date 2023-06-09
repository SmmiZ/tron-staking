<?php
declare(strict_types=1);

namespace App\Services\TronApi\Traits;

use App\Services\TronApi\Exception;
use App\Services\TronApi\Support\{Base58Check, BigInteger, Keccak};

trait TronAware
{
    /**
     * Convert from Hex
     *
     * @param $string
     * @return string
     */
    public function fromHex($string): string
    {
        if (strlen($string) == 42 && mb_substr($string, 0, 2) === '41') {
            return $this->hexString2Address($string);
        }

        return $this->hexString2Utf8($string);
    }

    /**
     * Convert to Hex
     *
     * @param string $address
     * @return string
     */
    public function toHex(string $address): string
    {
        if (mb_strlen($address) == 34 && mb_substr($address, 0, 1) === 'T') {
            return $this->address2HexString($address);
        }

        return $this->stringUtf8toHex($address);
    }

    /**
     * Check the address before converting to Hex
     *
     * @param $sHexAddress
     * @return string
     */
    public function address2HexString($sHexAddress): string
    {
        if (strlen($sHexAddress) == 42 && mb_strpos($sHexAddress, '41') == 0) {
            return $sHexAddress;
        }

        return Base58Check::decode($sHexAddress, 0, 3);
    }

    /**
     * Check Hex address before converting to Base58
     *
     * @param $sHexString
     * @return string
     */
    public function hexString2Address($sHexString): string
    {
        if (!ctype_xdigit($sHexString)) {
            return $sHexString;
        }

        if (strlen($sHexString) < 2 || (strlen($sHexString) & 1) != 0) {
            return '';
        }

        return Base58Check::encode($sHexString, 0, false);
    }

    /**
     * Convert string to hex
     *
     * @param $sUtf8
     * @return string
     */
    public function stringUtf8toHex($sUtf8): string
    {
        return bin2hex($sUtf8);
    }

    /**
     * Convert hex to string
     *
     * @param $sHexString
     * @return string
     */
    public function hexString2Utf8($sHexString): string
    {
        return hex2bin($sHexString);
    }

    /**
     * Convert to great value
     *
     * @param $str
     * @return BigInteger
     */
    public function toBigNumber($str): BigInteger
    {
        return new BigInteger($str);
    }

    /**
     * Convert SUN to TRX format
     *
     * @param int $amount
     * @return float
     */
    public function fromSun2Trx(int $amount): float
    {
        return (float)bcdiv((string)$amount, (string)1e6, 8);
    }

    /**
     * Convert TRX to SUN format
     *
     * @param $double
     * @return int
     */
    public function fromTrx2Sun($double): int
    {
        return (int)bcmul((string)$double, (string)1e6, 0);
    }

    /**
     * Convert to SHA3
     *
     * @param $string
     * @param bool $prefix
     * @return string
     * @throws \Exception
     */
    public function sha3($string, bool $prefix = true): string
    {
        return ($prefix ? '0x' : '') . Keccak::hash($string, 256);
    }

    /**
     * Закодировать массив в hexadecimal-строку
     *
     * @param array $data
     * @return string
     */
    public function encodeHexadecimal(array $data): string
    {
        $operations = str_repeat("\0", 32);

        foreach ($data as $operationId) {
            $byteIndex = intval($operationId / 8);
            $byteValue = $operations[$byteIndex];
            $newByteValue = chr(ord($byteValue) | (1 << $operationId % 8));
            $operations = substr_replace($operations, $newByteValue, $byteIndex, 1);
        }

        return bin2hex($operations);
    }

    /**
     * Раскодировать hexadecimal-строку в массив
     *
     * @param string $string
     * @return array
     */
    public function decodeHexadecimal(string $string): array
    {
        $operations = hex2bin($string);
        $result = [];

        for ($i = 0; $i < strlen($operations) * 8; $i++) {
            $byteIndex = intval($i / 8);
            $bitIndex = $i % 8;
            $byteValue = ord($operations[$byteIndex]);

            if (($byteValue & (1 << $bitIndex)) !== 0) {
                $result[] = $i;
            }
        }

        return $result;
    }

    public function trx2Energy(int $trxAmount): float
    {
        $resources = $this->getAccountResources();

        return $trxAmount * ($resources['TotalEnergyLimit'] / $resources['TotalEnergyWeight']);
    }

    public function energy2Trx(float $energyAmount): float
    {
        $resources = $this->getAccountResources();

        return $energyAmount / $resources['TotalEnergyLimit'] * $resources['TotalEnergyWeight'];
    }

    public function trx2Bandwidth(int $trxAmount): float
    {
        $resources = $this->getAccountResources();

        return $trxAmount * ($resources['TotalNetLimit'] / $resources['TotalNetWeight']);
    }

    public function bandwidth2Trx(float $bandwidthAmount): float
    {
        $resources = $this->getAccountResources();

        return $bandwidthAmount / $resources['TotalNetLimit'] * $resources['TotalNetWeight'];
    }
}
