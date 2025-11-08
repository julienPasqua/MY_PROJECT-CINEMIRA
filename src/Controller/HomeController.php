<?php

namespace App\Controller;

use App\Service\TmdbService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

final class HomeController extends AbstractController
{
    // 🏠 Page d'accueil
    #[Route('/', name: 'home.index')]
    public function index(Request $request, TmdbService $tmdbService): Response
    {
        $name = $request->query->get('name') ?? '';

        $movies = $tmdbService->getPopularMovies();
        $filmDuMois = $movies[0] ?? null;

        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'name' => $name,
            'movies' => $movies,
            'filmDuMois' => $filmDuMois,
        ]);
    }

    // 🔍 API de recherche TMDB
    #[Route('/api/tmdb/search', name: 'app.tmdb.search', methods: ['GET'])]
    public function search(Request $request, TmdbService $tmdbService): JsonResponse
    {
        $query = $request->query->get('query', '');

        if (strlen($query) < 2) {
            return $this->json([]);
        }

        // 🔹 Appel à ton service TMDB
        $movies = $tmdbService->searchMovies($query);

        return $this->json($movies);
    }
}
