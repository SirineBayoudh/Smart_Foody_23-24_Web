<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class ExchangeRateApiService
{
    private $httpClient;
    private $apiKey;

    public function __construct(HttpClientInterface $httpClient, string $apiKey)
    {
        $this->httpClient = $httpClient;
        $this->apiKey = $apiKey;
    }

    public function convertEURtoUSD(float $amount): ?float
    {
        $response = $this->httpClient->request('GET', 'https://api.exchangerate-api.com/v4/latest/EUR', [
            'query' => [
                'symbols' => 'USD',
                'access_key' => $this->apiKey
            ]
        ]);

        $content = $response->toArray();

        if (isset($content['rates']['USD'])) {
            return $amount * $content['rates']['USD'];
        }

        return null;
    }
}
