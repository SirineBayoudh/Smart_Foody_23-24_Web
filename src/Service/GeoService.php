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
        $response = $this->client->request('GET', 'https://ipapi.co/' . $ip . '/json/');
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

    public function getAdressFromIP($ip)
    {
        $response = $this->client->request('GET', 'https://ipapi.co/' . $ip . '/json/');
        $data = $response->toArray();

        $api_key = '';

        $latitude = $data['latitude'];
        $longitude = $data['longitude'];

        $response2 = $this->client->request('GET', 'https://api.opencagedata.com/geocode/v1/json?q=' . $latitude . '+' . $longitude . '&key=' . $api_key);
        $data2 = $response2->toArray();
        $normalized_city = $data2['results'][0]['components']['_normalized_city'];
        $state = $data2['results'][0]['components']['state'];
        return $normalized_city . ', ' . $state;
    }
}
