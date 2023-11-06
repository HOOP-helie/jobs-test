<?php

namespace App\Controller;

use App\Service\TokenService;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class JobsController extends AbstractController
{

    private $tokenService;

    public function __construct(TokenService $tokenService)
    {
        $this->tokenService = $tokenService;
    }

    // #[Route('/delete', name: 'delete_token')]
    // public function deleteToken(CacheInterface $cacheInterface): JsonResponse
    // {
    //     if ($cacheInterface->hasItem('api_token')) {
    //         $cacheInterface->deleteItem('api_token');
    //         return $this->json('Token supprimé avec succès du cache.');
    //     } else {
    //         return $this->json('Le token n\'existe pas dans le cache.');
    //     }
    // }


    // #[Route('/', name: 'home')]
    // public function index(CacheInterface $cache): JsonResponse
    // {
    //     $token = $cache->get('api_token', function (ItemInterface $item) {
    //         $item->expiresAfter(3600);
    //         return $this->getToken();
    //     });

    //     return $this->json($token);
    // }

    #[Route('/', name: 'homepage')]
    public function index(): RedirectResponse
    {
        // Redirection vers la route 'search_jobs'
        return $this->redirectToRoute('search_jobs');
    }


    #[Route('/jobs/search', name: 'search_jobs')]
    public function searchJobs(Request $request, CacheInterface $cache): Response
    {
        $searchForm = $this->createFormBuilder()
            ->add('what', TextType::class, [
                'required' => true,
                'label' => false,
                'attr' => array(
                    'placeholder' => 'Quel emploi recherchez-vous ?'
                )
            ])
            ->add('where', TextType::class, [
                'required' => true,
                'label' => false,
                'attr' => array(
                    'placeholder' => 'Où ?'
                )
            ])
            ->add('Rechercher', SubmitType::class)
            ->getForm();


        $searchForm->handleRequest($request);

        if ($searchForm->isSubmitted() && $searchForm->isValid()) {
            $userInput = $searchForm->getData();
            $dataAPI = $this->getJobs($cache, $userInput['what'], $userInput['where']);

            $jobsData = $dataAPI->data;
            $totalJobsFound = $jobsData->total;
            $jobsFound = $jobsData->ads;


            return $this->render('jobs.html.twig', [
                'searchForm' => $searchForm->createView(),
                'totalJobs' => $totalJobsFound,
                'jobs' => $jobsFound

            ]);
        }
        return $this->render('jobs.html.twig', [
            'searchForm' => $searchForm->createView(),
            'totalJobs' => null,
            'jobs' => null

        ]);
    }



    public function getJobs(CacheInterface $cache, $what, $where)
    {
        $api_url = $this->getParameter('api_url');

        $token = $cache->get('api_token', function (ItemInterface $item) {
            $item->expiresAfter(3600);
            return $this->tokenService->getToken();
        });

        $params = [
            'what' => $what,
            'where' => $where,
            'limit' => 5,
        ];

        // Récupération des offres
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $api_url . 'ads/search?' . http_build_query($params));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $token]);
        $response = json_decode(curl_exec($curl));
        // dd($response);
        if ($response->code == 200) {
            return $response;
        }
    }
}
