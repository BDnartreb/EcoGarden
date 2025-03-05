<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
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
/*
RETOURNE ERREUR
{
    "status": 500,
    "message": "Call to a member function getContent() on string"
}*/

//With Cache
    #[Route('/api/meteo/{zipcode}', name: 'api_meteo_zipcode', methods:['GET'])]
    //public function getApiMeteo(string $zipcode, CacheInterface $cache): JsonResponse
    public function getApiMeteo(?string $zipcode, TagAwareCacheInterface $cache): JsonResponse
    {
        $idCache = "getApiMeteo-" . $zipcode;
        $apiKey = $this->apiKey;

        $jsonMeteo = $cache->get($idCache, function(ItemInterface $item) use ($zipcode, $apiKey)
        {
            echo ("L\'ELEMENT N\'EST PAS ENCORE EN CACHE!\n");
            $item->expiresAfter(3600);
            $item->tag("meteoCache");
            //gets latitude and longitude coordinates of the city from the zipcode
            //https://openweathermap.org/api/geocoding-api
            $coordinates = $this->client->request(
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
            $weatherResponse = $this->client->request(
                'GET',
            //Current wheather request
                //'https://api.openweathermap.org/data/2.5/weather?lat=' . $lat . '&lon=' . $lon . '&appid=' . $apiKey .''
            //3 hours forecast for 5 days request
            sprintf(
                'https://api.openweathermap.org/data/2.5/forecast?lat=%s&lon=%s&appid=%s',
                $lat,
                $lon,
                $this->apiKey
                )
            );
            return new JsonResponse($weatherResponse->getContent(), $weatherResponse->getStatusCode(), [], true);
        });
        return new JsonResponse($jsonMeteo, JsonResponse::HTTP_OK, [], true);
    }

    #[Route('/api/meteo/', name: 'api_meteo_default', methods:['GET'])]
    public function getApiMeteoDefaut(): RedirectResponse
    {
        $user = $this->getUser();
        $zipcode = $user->getZipcode();
        return $this->redirectToRoute('api_meteo_zipcode', ['zipcode' => $zipcode]);
    }

//Without Cache
/*    #[Route('/api/meteo/nocache/{zipcode}', name: 'api_meteo_zipcode_nocache', methods:['GET'])]
    public function getApiMeteoNoCache(string $zipcode): JsonResponse
    {
        $apiKey = $this->apiKey;

            //gets latitude and longitude coordinates of the city from the zipcode
            //https://openweathermap.org/api/geocoding-api
            $coordinates = $this->client->request(
                'GET',
                //'http://api.openweathermap.org/geo/1.0/zip?zip=' . $zipcode . ',FR&appid=' . $apiKey .''
                sprintf(
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
            $weatherResponse = $this->client->request(
                'GET',
            //Current wheather request
                //'https://api.openweathermap.org/data/2.5/weather?lat=' . $lat . '&lon=' . $lon . '&appid=' . $apiKey .''
            //3 hours forecast for 5 days request
            sprintf(
                'https://api.openweathermap.org/data/2.5/forecast?lat=%s&lon=%s&appid=%s',
                $lat,
                $lon,
                $this->apiKey
                )
            );

        return new JsonResponse($weatherResponse->getContent(), $weatherResponse->getStatusCode(), [], true);
    }
*/

}
