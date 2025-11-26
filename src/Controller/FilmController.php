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
    /**
     * Affiche la liste de tous les films
     */
    #[Route('/films', name: 'app_films_index')]
    public function index(FilmRepository $filmRepo): Response
    {
        $films = $filmRepo->findAll();

        return $this->render('film/index.html.twig', [
            'films' => $films,
        ]);
    }

    /**
     * Affiche les détails d'un film EN BDD (id local)
     */
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
            'tmdbId' => $film->getTmdbId(), // peut servir ailleurs si besoin
        ]);
    }

    /**
     * Affiche un film par TMDB (si pas en BDD)
     */
    #[Route('/film/tmdb/{tmdbId}', name: 'app_film_show_tmdb', requirements: ['tmdbId' => '\d+'])]
    public function showByTmdbId(
        int $tmdbId,
        FilmRepository $filmRepo,
        TmdbService $tmdbService
    ): Response {

        // 1️⃣ Si déjà en BDD → on redirige vers la route normale
        $filmEntity = $filmRepo->findByTmdbId($tmdbId);

        if ($filmEntity) {
            return $this->redirectToRoute('app_film_show', [
                'id' => $filmEntity->getId(),
            ]);
        }

        // 2️⃣ Sinon, on va chercher dans TMDB
        $movieData = $tmdbService->getMovieDetails($tmdbId);

        if (!$movieData) {
            throw $this->createNotFoundException('Film introuvable sur TheMovieDB.');
        }

        // 3️⃣ On NORMALISE les données pour le Twig
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
            // pas d'avis pour la version TMDB brute
            'avis' => [],
            'ratingDistribution' => [],
        ]);
    }

    /**
     * Recherche
     */
    #[Route('/films/search', name: 'app_films_search')]
    public function search(
        FilmRepository $filmRepo,
        TmdbService $tmdbService
    ): Response {
        $query = $_GET['q'] ?? '';

        if (!$query) {
            return $this->redirectToRoute('app_films_index');
        }

        $filmsLocal = $filmRepo->searchByTitre($query);
        $filmsTmdb  = $tmdbService->searchMovies($query);

        return $this->render('film/search.html.twig', [
            'query'      => $query,
            'filmsLocal' => $filmsLocal,
            'filmsTmdb'  => $filmsTmdb,
        ]);
    }

    /**
     * Films par genre
     */
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

    #[Route('/api/tmdb/search', name: 'api_tmdb_search')]
    public function apiSearch(Request $request, TmdbService $tmdbService): JsonResponse
    {
        $query = $request->query->get('query', '');

        if (strlen($query) < 2) {
        return $this->json([]);
        }

        $results = $tmdbService->searchMovies($query);

        $formatted = array_map(function ($movie) {
        return [
            'id'           => $movie['id'] ?? null,
            'title'        => $movie['title'] ?? '',
            'overview'     => $movie['overview'] ?? '',
            'poster_path'  => $movie['poster_path'] ?? null,
            'release_date' => $movie['release_date'] ?? null,
        ];
        }, $results ?? []);

        return $this->json($formatted);
    }

}
