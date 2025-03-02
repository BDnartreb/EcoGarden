<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class ApiMeteoController extends AbstractController
{
    private HttpClientInterface $client;
    private string $apiKey;

    public function __construct(HttpClientInterface $client)
    {
        // Initialise HTTP client
        //$this->client = HttpClient::create();
        $this->client = $client;
        // Gets api key from .env.local
        $this->apiKey = $_ENV['API_KEY'] ?? '';
    }

    #[Route('/api/meteo/{zipcode}', name: 'api_meteo_zipcode', methods:['GET'])]
    public function getApiMeteo(string $zipcode, HttpClientInterface $httpClient): JsonResponse
    {
        /*if ($zipcode === null) {
            $zipcode = 05000;
        }*/
        $apiKey = $this->apiKey;

        //gets latitude and longitude coordinates of the city from the zipcode
        //https://openweathermap.org/api/geocoding-api
        $coordinates = $httpClient->request(
            'GET',
            sprintf(
            //'http://api.openweathermap.org/geo/1.0/zip?zip=' . $zipcode . ',FR&appid=' . $apiKey .''
            'http://api.openweathermap.org/geo/1.0/zip?zip=%s,FR&appid=%s',
            $zipcode,
            $apiKey
            )
        );
        
        $locationArray = $coordinates->toArray();
        $lat = $locationArray['lat'] ?? null;
        $lon = $locationArray['lon'] ?? null;

        if (!$lat || !$lon) {
            return new JsonResponse(['error' => 'Coordinates not found'], JsonResponse::HTTP_BAD_REQUEST);
        }

        //Gets weather data form the site https://openweathermap.org/current
        $response = $httpClient->request(
            'GET',
        //Current wheather
            //'https://api.openweathermap.org/data/2.5/weather?lat=' . $lat . '&lon=' . $lon . '&appid=' . $apiKey .''
        //3 hours forecast for 5 days
        sprintf(
            'https://api.openweathermap.org/data/2.5/forecast?lat=%s&lon=%s&appid=%s',
            $lat,
            $lon,
            $this->apiKey
            )
        );
       
        return new JsonResponse($response->getContent(), $response->getStatusCode(), [], true);
    }

    /*#[Route('/api/meteo', name: 'api_meteo_default', methods:['GET'])]
    public function getApiMeteoDefaut(): JsonResponse
    {
        //$zipcode = '05000';
        return $this->redirectToRoute('api_meteo_zipcode', ['zipcode' => '05000']);
    }*/
}
