<?php

namespace App\Service;

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
            throw new \Exception("Le service est temporairement indisponible. Veuillez réessayer plus tard.");
        }

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $this->apiUrl . 'ads/search?' . http_build_query(['what' => $what, 'where' => $where, 'page' => $page, 'limit' => $limit]));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $token]);

        $response = curl_exec($curl);

        if ($response === false) {
            // $error = curl_error($curl);
            // Ajouter un logger
            curl_close($curl);
            throw new \Exception("Les offres d'emploi n'ont pas pu être récupérées. Veuillez rééssayer plus tard.");
        }

        $decodedResponse = json_decode($response);
        curl_close($curl);

        if (isset($decodedResponse->code) && $decodedResponse->code == 200) {
            return $decodedResponse;
        } else {
            // Ajouter un logger
            throw new \Exception("La requête a échoué. Veuillez réssayer plus tard.");
        }
    }
}
