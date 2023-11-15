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

    // Tester en cas de suppression du cache
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
        // Redirection vers la route 'search_jobs' pour le moment
        return $this->redirectToRoute('search_jobs');
    }

    #[Route('/jobs/search', name: 'search_jobs')]
    public function searchJobs(Request $request): Response
    {
        // Crée le formulaire
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

        // Initialise les variables si params sont présents dans l'url
        $what = $request->query->get('what');
        $where = $request->query->get('where');
        $currentPage = $request->query->get('page');

        try {
            // Si le formulaire est soumis et valide
            if ($searchForm->isSubmitted() && $searchForm->isValid()) {

                // Récupère les données du formulaire
                $userInput = $searchForm->getData();

                // Redirige vers une URL et les données du formulaire en paramètres
                return $this->redirectToRoute('search_jobs', [
                    'what' =>  $userInput['what'],
                    'where' =>  $userInput['where'],
                    'page' => 1
                ]);

                // Sinon, prendre les params de l'url 
            } elseif ($what && $where && $currentPage) {

                // Appel API pour récupérer les offres selon les paramètres de l'url
                $dataAPI = $this->jobsService->getJobs($what, $where, $currentPage);
                $jobsData = $dataAPI->data;
                $totalJobsFound = $jobsData->total;
                $jobsFound = $jobsData->ads;

                //Affichage des offres trouvées
                return $this->render('jobs.html.twig', [
                    'searchForm' => $searchForm->createView(),
                    'totalJobs' => $totalJobsFound,
                    'jobs' => $jobsFound,
                    'currentPage' => $currentPage,
                    'what' => $what,
                    'where' => $where
                ]);
                // Si pas de soumission de formulaire ou les paramères nécessaires dans l'url, rendre le template de base
            } else {
                return $this->render('jobs.html.twig', [
                    'searchForm' => $searchForm->createView()
                ]);
            }
            // Affiche un message d'erreur et le template de base 
        } catch (\Exception $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->render('jobs.html.twig', [
                'searchForm' => $searchForm->createView()
            ]);
        }
    }
}
