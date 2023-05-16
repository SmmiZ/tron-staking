<?php

declare(strict_types=1);

namespace App\Services\TronApi\Provider;

use GuzzleHttp\{Exception\GuzzleException, Psr7\Request, Client};
use Psr\Http\Message\StreamInterface;
use App\Services\TronApi\Exception\{NotFoundException, TronException};
use App\Services\TronApi\Support\Utils;

class HttpProvider implements HttpProviderInterface
{
    /**
     * HTTP Client Handler
     *
     * @var Client.
     */
    protected Client $httpClient;

    /**
     * Server or RPC URL
     *
     * @var string
    */
    protected string $host;

    /**
     * Waiting time
     *
     * @var int
     */
    protected int $timeout = 30000;

    /**
     * Get custom headers
     *
     * @var array
    */
    protected array $headers = [];

    /**
     * Get the pages
     *
     * @var string
    */
    protected string $statusPage = '/';

    /**
     * Create an HttpProvider object
     *
     * @param string $host
     * @throws TronException
     */
    public function __construct(string $host)
    {
        if (!Utils::isValidUrl($host)) {
            throw new TronException('Invalid URL provided to HttpProvider');
        }

        $this->host = $host;

        $this->httpClient = new Client([
            'base_uri' => $host,
            'timeout' => $this->timeout,
            'auth' => false
        ]);
    }

    /**
     * Enter a new page
     *
     * @param string $page
     */
    public function setStatusPage(string $page = '/'): void
    {
        $this->statusPage = $page;
    }

    /**
     * Check connection
     *
     * @return bool
     * @throws TronException|GuzzleException
     */
    public function isConnected(): bool
    {
        $response = $this->request($this->statusPage);

        return array_key_exists('blockID', $response) || array_key_exists('status', $response);
    }

    /**
     * Getting a host
     *
     * @return string
    */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * Getting timeout
     *
     * @return int
    */
    public function getTimeout(): int
    {
        return $this->timeout;
    }

    /**
     * We send requests to the server
     *
     * @param $url
     * @param array $payload
     * @param string $method
     * @return array
     * @throws TronException
     * @throws GuzzleException
     */
    public function request($url, array $payload = [], string $method = 'get'): array
    {
        $method = strtoupper($method);

        if (!in_array($method, ['GET', 'POST'])) {
            throw new TronException('The method is not defined');
        }

        $options = [
            'headers' => $this->headers,
            'body' => json_encode($payload)
        ];

        $request = new Request($method, $url, $options['headers'], $options['body']);
        $rawResponse = $this->httpClient->send($request, $options);

        return $this->decodeBody(
            $rawResponse->getBody(),
            $rawResponse->getStatusCode()
        );
    }

    /**
     * Convert the original answer to an array
     *
     * @param StreamInterface $stream
     * @param int $status
     * @return array
     */
    protected function decodeBody(StreamInterface $stream, int $status): array
    {
        $decodedBody = json_decode($stream->getContents(), true);

        if ((string)$stream == 'OK') {
            $decodedBody = [
                'status' => 1
            ];
        } elseif ($decodedBody == null || !is_array($decodedBody)) {
            $decodedBody = [];
        }

        if ($status == 404) {
            throw new NotFoundException('Page not found');
        }

        return $decodedBody;
    }
}
