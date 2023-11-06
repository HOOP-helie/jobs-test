<?php

namespace App\Controller;

use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Contracts\Cache\ItemInterface;

class JobsController extends AbstractController
{
    public function getToken(): ?string
    {
        $clientId = $this->getParameter('client_id');
        $clientSecret =  $this->getParameter('client_secret');
        $api_url = $this->getParameter('api_url');

        // Récupération du token
        try {
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $api_url . 'login');
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode(['client_id' =>
            $clientId, 'client_secret' => $clientSecret]));
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

    #[Route('/delete', name: 'delete_token')]
    public function deleteToken(CacheInterface $cacheInterface): JsonResponse
    {
        if ($cacheInterface->hasItem('api_token')) {
            $cacheInterface->deleteItem('api_token');
            return $this->json('Token supprimé avec succès du cache.');
        } else {
            return $this->json('Le token n\'existe pas dans le cache.');
        }
    }


    #[Route('/', name: 'home')]
    public function index(CacheInterface $cache): JsonResponse
    {

        $token = $cache->get('api_token', function (ItemInterface $item) {
            $item->expiresAfter(3600);
            return $this->getToken();
        });

        return $this->json($token);
    }
}
