<?php

namespace App\Controller;

use App\Repository\FilmRepository;
use App\Repository\AvisRepository;
use App\Repository\GenreRepository;
use App\Service\TmdbService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class FilmController extends AbstractController
{
    #[Route('/films', name: 'app_films_index')]
    public function index(FilmRepository $filmRepo): Response
    {
        $films = $filmRepo->findAll();

        return $this->render('film/index.html.twig', [
            'films' => $films,
        ]);
    }

    #[Route('/film/{id}', name: 'app_film_show', requirements: ['id' => '\d+'])]
    public function show(
        int $id,
        FilmRepository $filmRepo,
        AvisRepository $avisRepo
    ): Response {

        $film = $filmRepo->findWithRelations($id);

        if (!$film) {
            throw $this->createNotFoundException('Film introuvable.');
        }

        $avis = $avisRepo->findRecentByFilm($film, 5);
        $ratingDistribution = $avisRepo->getRatingDistribution($film);

        return $this->render('film/show.html.twig', [
            'film' => $film,
            'avis' => $avis,
            'ratingDistribution' => $ratingDistribution,
            'tmdbId' => $film->getTmdbId(),
        ]);
    }

    #[Route('/film/tmdb/{tmdbId}', name: 'app_film_show_tmdb', requirements: ['tmdbId' => '\d+'])]
    public function showByTmdbId(
        int $tmdbId,
        FilmRepository $filmRepo,
        TmdbService $tmdbService
    ): Response {

        $filmEntity = $filmRepo->findByTmdbId($tmdbId);

        // Si déjà en BDD, redirection propre
        if ($filmEntity) {
            return $this->redirectToRoute('app_film_show', [
                'id' => $filmEntity->getId(),
            ]);
        }

        $movieData = $tmdbService->getMovieDetails($tmdbId);

        if (!$movieData) {
            throw $this->createNotFoundException('Film introuvable sur TMDB.');
        }

        $film = [
            'tmdbId'      => $movieData['id'],
            'titre'       => $movieData['title'],
            'dateSortie'  => $movieData['release_date'] ?? null,
            'synopsis'    => $movieData['overview'] ?? '',
            'posterUrl'   => $movieData['poster_path'] ?? null,
            'backdropUrl' => $movieData['backdrop_path'] ?? null,
            'genres'      => $movieData['genres'] ?? [],
            'duree'       => $movieData['runtime'] ?? null,
            'note'        => $movieData['vote_average'] ?? null,
        ];

        return $this->render('film/show.html.twig', [
            'film' => $film,
            'avis' => [],
            'ratingDistribution' => [],
            'tmdbId' => $tmdbId,
        ]);
    }

    /**
     * 🔵 Recherche PUBLIC (Navbar) — JSON
     * Route utilisée par ton JS dans la navbar
     */
    #[Route('/api/tmdb/search', name: 'api_tmdb_search')]
    public function apiSearch(Request $request, TmdbService $tmdbService): JsonResponse
    {
        $query = $request->query->get('query', '');

        if (strlen($query) < 2) {
            return $this->json([]);
        }

        return $this->json($tmdbService->searchMovies($query));
    }

    /**
     * 🟠 Recherche ADMIN TMDB — JSON
     * Utilisée dans admin/séance/new
     */
    #[Route('/search/admin', name: 'film_search_admin')]
    public function searchAdmin(Request $request, TmdbService $tmdbService): JsonResponse
    {
        $query = $request->query->get('q', '');

        if (strlen($query) < 2) {
            return $this->json([]);
        }

        return $this->json($tmdbService->searchMovies($query));
    }

    #[Route('/films/genre/{id}', name: 'app_films_by_genre')]
    public function byGenre(int $id, GenreRepository $genreRepo, FilmRepository $filmRepo): Response
    {
        $genre = $genreRepo->find($id);

        if (!$genre) {
            throw $this->createNotFoundException('Genre introuvable.');
        }

        $films = $filmRepo->findByGenre($genre);

        return $this->render('film/by_genre.html.twig', [
            'genre' => $genre,
            'films' => $films,
        ]);
    }
}
