<?php

namespace App\Controller;

use App\Entity\Seance;
use App\Entity\Film;
use App\Form\SeanceType;
use App\Repository\SeanceRepository;
use App\Service\TmdbService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use DateTimeImmutable;

#[Route('/admin/seance')]
class SeanceController extends AbstractController
{
    #[Route('/', name: 'app_admin_seance_index', methods: ['GET'])]
    public function index(SeanceRepository $seanceRepository): Response
    {
        return $this->render('admin/seance/index.html.twig', [
            'seances' => $seanceRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_admin_seance_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em, TmdbService $tmdbService): Response
    {
        $seance = new Seance();
        $form = $this->createForm(SeanceType::class, $seance);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $tmdbId = $form->get('tmdb_id')->getData();

            if ($tmdbId) {
                // 1. Chercher si le film existe déjà en BDD
                $film = $em->getRepository(Film::class)->findOneBy(['tmdb_id' => $tmdbId]);

                // 2. Si non, le créer depuis l'API TMDB
                if (!$film) {
                    $data = $tmdbService->getMovieDetails($tmdbId);

                    if ($data) {
                        $film = new Film();
                        $film->setTmdbId($tmdbId);
                        
                        // Utiliser les données de TMDB (pas $request)
                        $film->setTitre($data['title'] ?? 'Sans titre');
                        //dd(new \DateTimeImmutable());
                        $film->setDateCreation(new \DateTimeImmutable());
                        $film->setSynopsis($data['overview'] ?? '');
                        $film->setPosterUrl(
                            $tmdbService->getPosterUrl($data['poster_path'] ?? null)
                        );

                        if (!empty($data['release_date'])) {
                            $film->setDateSortie(new \DateTime($data['release_date']));
                        }

                        $em->persist($film);
                    }
                }

                // 3. Associer le film à la séance (seulement si $film existe)
                if ($film) {
                    $seance->setFilm($film);
                } else {
                    // Pas de film trouvé → erreur
                    $this->addFlash('error', 'Impossible de récupérer les infos du film.');
                    return $this->render('admin/seance/new.html.twig', [
                        'form' => $form->createView(),
                    ]);
                }
            } else {
                // Pas de tmdb_id → erreur
                $this->addFlash('error', 'Veuillez sélectionner un film.');
                return $this->render('admin/seance/new.html.twig', [
                    'form' => $form->createView(),
                ]);
            }

            $em->persist($seance);
            $em->flush();

            $this->addFlash('success', 'Séance créée avec succès !');
            return $this->redirectToRoute('app_admin_seance_index');
        }

        return $this->render('admin/seance/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/search-tmdb', name: 'app_admin_seance_search_tmdb', methods: ['GET'])]
    public function searchTMDB(Request $request, TmdbService $tmdbService): JsonResponse
    {
        $query = $request->query->get('tmdb_query');

        if (strlen($query) < 2) {
            return $this->json([]);
        }

        return $this->json($tmdbService->searchMovies($query));
    }

    #[Route('/{id}/edit', name: 'app_admin_seance_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Seance $seance, EntityManagerInterface $em, TmdbService $tmdbService): Response
    {
        $form = $this->createForm(SeanceType::class, $seance);
        $form->handleRequest($request);

        $currentFilm = null;
        if ($seance->getFilm() && $seance->getFilm()->getTmdbId()) {
            $currentFilm = $tmdbService->getMovieDetails($seance->getFilm()->getTmdbId());
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Séance modifiée avec succès !');
            return $this->redirectToRoute('app_admin_seance_index');
        }

        return $this->render('admin/seance/edit.html.twig', [
            'form' => $form->createView(),
            'seance' => $seance,
            'current_film' => $currentFilm,
        ]);
    }



     #[Route('/{id}/delete', name: 'app_admin_seance_delete', methods: ['POST'])]
    public function delete(Request $request, Seance $seance, EntityManagerInterface $em): Response
    {
        // Vérification du token CSRF
        if ($this->isCsrfTokenValid('delete' . $seance->getId(), $request->request->get('_token'))) {
            $em->remove($seance);
            $em->flush();
            $this->addFlash('success', 'Séance supprimée avec succès !');
        } else {
            $this->addFlash('error', 'Token CSRF invalide.');
        }

        return $this->redirectToRoute('app_admin_seance_index');
    }
}