<?php

namespace App\Service;

use GuzzleHttp\Client;

class CalorieNinjasService
{
    private $client;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => 'https://api.calorieninjas.com/v1/'
        ]);
    }

    public function getCaloriesForFood($foodName)
    {
        $response = $this->client->request('GET', 'nutrition', [
            'query' => [
                'query' => $foodName
            ],
            'headers' => [
                'X-Api-Key' => 'loKcPg9+ANvuoq3nO+ikzw==ZdYlZ1IAZDp1pfE1'
            ]
        ]);

        $data = json_decode($response->getBody()->getContents(), true);

        if (!empty($data['items']) && isset($data['items'][0]['calories'])) {
            return $data['items'][0]['calories'];
        }
    }
}
