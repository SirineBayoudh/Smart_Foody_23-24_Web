<?php

namespace App\Service;

use GuzzleHttp\Client;

class QuotableService
{
    private $client;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => 'https://api.quotable.io/'
        ]);
    }

    public function getRandomQuote(): ?string
    {
        $response = $this->client->request('GET', 'random?tags=inspirational|motivational');
        $data = json_decode($response->getBody()->getContents(), true);

        return $data['content'] ?? null;
    }
}
