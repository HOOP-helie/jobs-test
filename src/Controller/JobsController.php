<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class JobsController extends AbstractController
{
    #[Route('/', name: 'app_jobs')]
    public function index(): JsonResponse
    {
        $clientId = "d21517c83e5a991cf51cdf6d1d5a2037";
        $clientSecret = "";
        $api_url = 'https://api.jobijoba.com/v3/fr/login';

        //Récupération du token
        try {
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $api_url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode(['client_id' =>
            $clientId, 'client_secret' => $clientSecret]));
            $response = json_decode(curl_exec($curl));
            curl_close($curl);

            if ($response->code == 200) {
                return $this->json([
                    'message' => $response->token,
                ]);
            }

            return $this->json([
                'message' => $response->message,
            ]);
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }
}
