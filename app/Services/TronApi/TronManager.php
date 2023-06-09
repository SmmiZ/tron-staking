<?php

namespace App\Services\TronApi;

use App\Services\TronApi\Exception\TronException;
use GuzzleHttp\Exception\GuzzleException;
use App\Services\TronApi\Provider\{HttpProvider, HttpProviderInterface};

class TronManager
{
    /**
     * Providers
     *
     * @var array
     */
    protected array $providers = [
        'fullNode' => [],
        'solidityNode' => [],
        'eventServer' => [],
        'explorer' => [],
        'signServer' => []
    ];

    /**
     * Status Page
     *
     * @var array
     */
    protected array $statusPage = [
        'fullNode' => 'wallet/getnowblock',
        'solidityNode' => 'walletsolidity/getnowblock',
        'eventServer' => 'healthcheck',
        'explorer' => 'api/system/status'
    ];

    public function __construct(HttpProvider $provider)
    {
        $this->providers = $providers = [
            'fullNode' => $provider,
            'solidityNode' => $provider,
            'eventServer' => $provider,
        ];

        foreach ($providers as $key => $value) {
            $this->providers[$key]->setStatusPage($this->statusPage[$key]);
        }
    }

    /**
     * List of providers
     *
     * @return array
     */
    public function getProviders(): array
    {
        return $this->providers;
    }

    /**
     * Full Node
     *
     * @throws TronException
     * @return HttpProviderInterface
     */
    public function fullNode(): HttpProviderInterface
    {
        if (!array_key_exists('fullNode', $this->providers)) {
            throw new TronException('Full node is not activated.');
        }

        return $this->providers['fullNode'];
    }

    /**
     * Solidity Node
     *
     * @throws TronException
     * @return HttpProviderInterface
     */
    public function solidityNode(): HttpProviderInterface
    {
        if (!array_key_exists('solidityNode', $this->providers)) {
            throw new TronException('Solidity node is not activated.');
        }

        return $this->providers['solidityNode'];
    }

    /**
     * Sign server
     *
     * @throws TronException
     * @return HttpProviderInterface
     */
    public function signServer(): HttpProviderInterface
    {
        if (!array_key_exists('signServer', $this->providers)) {
            throw new TronException('Sign server is not activated.');
        }

        return $this->providers['signServer'];
    }

    /**
     * TronScan server
     *
     * @throws TronException
     * @return HttpProviderInterface
     */
    public function explorer(): HttpProviderInterface
    {
        if (!array_key_exists('explorer', $this->providers)) {
            throw new TronException('Explorer is not activated.');
        }

        return $this->providers['explorer'];
    }

    /**
     * Event server
     *
     * @throws TronException
     * @return HttpProviderInterface
     */
    public function eventServer(): HttpProviderInterface
    {
        if (!array_key_exists('eventServer', $this->providers)) {
            throw new TronException('Event server is not activated.');
        }

        return $this->providers['eventServer'];
    }

    /**
     * Basic query to nodes
     *
     * @param $url
     * @param array $params
     * @param string $method
     * @return array
     * @throws TronException
     */
    public function request($url, array $params = [], string $method = 'post'): array
    {
        $split = explode('/', $url);

        //todo если не будут использоваться варианты кроме fullNode, то можно вырезать лишние проверки и методы
        return match (true) {
            in_array($split[0], ['walletsolidity', 'walletextension']) => $this->solidityNode()->request($url, $params, $method),
            $split[0] == 'event' => $this->eventServer()->request($url, $params),
            $split[0] == 'trx-sign' => $this->signServer()->request($url, $params, 'post'),
            $split[0] == 'api' => $this->explorer()->request($url, $params),
            default => $this->fullNode()->request($url, $params, $method),
        };
    }

    /**
     * Check connections
     *
     * @return array
     * @throws TronException|GuzzleException
     */
    public function isConnected(): array
    {
        $array = [];
        foreach ($this->providers as $key => $value) {
            $array[] = [
                $key => boolval($value->isConnected())
            ];
        }

        return $array;
    }
}
