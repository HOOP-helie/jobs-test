<?php

namespace App\Service;

class JobsService
{
    private $apiUrl;

    public function __construct(string $apiUrl)
    {
        $this->apiUrl = $apiUrl;
    }

    public function getJobs(string $token, string $what, string $where, int $limit = 5)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $this->apiUrl . 'ads/search?' . http_build_query(['what' => $what, 'where' => $where, 'limit' => $limit]));
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
