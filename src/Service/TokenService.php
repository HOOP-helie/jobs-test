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
        try {
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $this->apiUrl . 'login');
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode(['client_id' =>
            $this->clientId, 'client_secret' => $this->clientSecret]));
            $response = json_decode(curl_exec($curl));
            curl_close($curl);

            if ($response->code == 200 && isset($response->token)) {
                return $response->token;
            }
            return null;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
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
