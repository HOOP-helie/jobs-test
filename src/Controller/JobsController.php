<?php

namespace App\Controller;

use App\Service\JobsService;
// use Symfony\Contracts\Cache\ItemInterface;
// use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
// use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class JobsController extends AbstractController
{
    private $jobsService;

    public function __construct(JobsService $jobsService)
    {
        $this->jobsService = $jobsService;
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


    #[Route('/', name: 'homepage')]
    public function index(): RedirectResponse
    {
        // Redirection vers la route 'search_jobs'
        return $this->redirectToRoute('search_jobs');
    }

    #[Route('/jobs/search', name: 'search_jobs')]
    public function searchJobs(Request $request): Response
    {
        // Créée le formulaire
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

        // Gère la soumission du formulaire
        $searchForm->handleRequest($request);
        $currentPage = 1;
        $what = $request->query->get('what');
        $where = $request->query->get('where');

        // Si le formulaire est soumis et valide
        if ($searchForm->isSubmitted() && $searchForm->isValid()) {
            $userInput = $searchForm->getData();
            // Faire une requête avec les données du formulaire
            $what = $userInput['what'];
            $where =  $userInput['where'];
            $dataAPI = $this->jobsService->getJobs($what, $where);

            // Sinon, prendre les params de l'url
        } elseif ($what && $where) {
            $currentPage = $request->query->get('page');
            $dataAPI = $this->jobsService->getJobs($what, $where, $currentPage);

            // Sinon, rendre le template de base
        } else {
            return $this->render('jobs.html.twig', [
                'searchForm' => $searchForm->createView()
            ]);
        }

        //Todo : Gérer le cas où l'api ne retourne une erreur

        //Todo : Gérer le cas où les données ci-dessous n'existent pas
        $jobsData = $dataAPI->data;
        $totalJobsFound = $jobsData->total;
        $jobsFound = $jobsData->ads;

        return $this->render('jobs.html.twig', [
            'searchForm' => $searchForm->createView(),
            'totalJobs' => $totalJobsFound,
            'jobs' => $jobsFound,
            'currentPage' => $currentPage,
            'what' => $what,
            'where' => $where
        ]);
    }
}
