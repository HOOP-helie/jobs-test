<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class TokenService
{
    private $clientId;
    private $clientSecret;
    private $apiUrl;
    private $cache;

    public function __construct(CacheInterface $cache, string $clientId, string $clientSecret, string $apiUrl)
    {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->apiUrl = $apiUrl;
        $this->cache = $cache;
    }

    public function getTokenFromAPI(): ?string
    {

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $this->apiUrl . 'login');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode(['client_id' =>
        $this->clientId, 'client_secret' => $this->clientSecret]));

        $response = curl_exec($curl);

        // Si la requête a échoué
        if ($response === false) {
            // $error = curl_error($curl);
            // Ajouter un logger avec $error
            curl_close($curl);
            throw new \Exception("Le service est temporairement indisponible. Veuillez réessayer plus tard.");
        }

        $decodedResponse = json_decode($response);
        curl_close($curl);

        // Si la requête a réussi mais qu'on ne trouve pas de token
        if (!isset($decodedResponse->token)) {
            // Ajouter un logger
            throw new \Exception("Le service est temporairement indisponible. Veuillez réessayer plus tard..");
        }

        return $decodedResponse->token;
    }

    public function getToken()
    {
        $response = $this->cache->get('api_token', function (ItemInterface $item) {
            // Expire après 50min
            $item->expiresAfter(3000);
            return $this->getTokenFromAPI();
        });

        return $response;
    }
}
