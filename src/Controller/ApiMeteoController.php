<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class ApiMeteoController extends AbstractController
{
    #[Route('/api/meteo', name: 'api_meteo', methods:['GET'])]
    public function getApiMeteo(HttpClientInterface $httpCLient): JsonResponse
    {
        $response = $httpClient->request(
            'GET',
            'https://home.openweathermap.org/'
        );
        return new JsonResponse($response->getContent(), $response->getStatusCode(), [], true);
    }
}
