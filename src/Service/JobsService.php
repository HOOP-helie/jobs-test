<?php

namespace App\Service;

use LDAP\Result;

class JobsService
{
    private $apiUrl;
    private $tokenService;

    public function __construct(TokenService $tokenService, string $apiUrl)
    {
        $this->apiUrl = $apiUrl;
        $this->tokenService = $tokenService;
    }

    public function getJobs(string $what, string $where, int $page = 1, int $limit = 10)
    {
        $token = $this->tokenService->getToken();

        if (!$token) {
            return null;
        }

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $this->apiUrl . 'ads/search?' . http_build_query(['what' => $what, 'where' => $where, 'page' => $page, 'limit' => $limit]));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $token]);

        $response = json_decode(curl_exec($curl));
        curl_close($curl);

        if (isset($response->code) && $response->code == 200) {
            return $response;
        }

        return null;
    }
}
