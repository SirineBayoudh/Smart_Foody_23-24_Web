<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class GeoService
{
    private $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    public function getCountryNameFromIp($ip)
    {
        $response = $this->client->request('GET', 'https://freegeoip.app/json/' . $ip);
        $data = $response->toArray(); // Convertit la réponse JSON en tableau PHP
        return $data['country_name'] ?? null;
    }

    public function getPhoneCodeFromCountryName($countryName)
    {
        $response = $this->client->request('GET', 'https://restcountries.com/v2/name/' . urlencode($countryName));
        $data = $response->toArray();
        return $data[0]['callingCodes'][0] ?? null; // Supposons que la première entrée est la bonne
    }

    public function getFlagFromCountryName($countryName)
    {
        $response = $this->client->request('GET', 'https://restcountries.com/v2/name/' . urlencode($countryName));
        $data = $response->toArray();
        return $data[0]['flags']['svg'] ?? null; // Supposons que la première entrée est la bonne
    }
}
