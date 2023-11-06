<?php

namespace App\Service;

class TokenService
{
    private $clientId;
    private $clientSecret;
    private $apiUrl;

    public function __construct(string $clientId, string $clientSecret, string $apiUrl)
    {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->apiUrl = $apiUrl;
    }

    public function getToken(): ?string
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
            return null;
        }
    }
}
