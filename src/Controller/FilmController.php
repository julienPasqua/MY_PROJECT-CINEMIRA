<?php

namespace App\Controller;

use App\Repository\FilmRepository;
use App\Repository\SeanceRepository;
use App\Repository\AvisRepository;
use App\Repository\GenreRepository;
use App\Service\TmdbService;
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
     * Affiche les détails d'un film avec ses séances
     */
    #[Route('/film/{id}', name: 'app_film_show', requirements: ['id' => '\d+'])]
    public function show(
        int $id,
        FilmRepository $filmRepo,
        SeanceRepository $seanceRepo,
        AvisRepository $avisRepo
    ): Response {
        // Récupérer le film avec toutes ses relations
        $film = $filmRepo->findWithRelations($id);

        if (!$film) {
            throw $this->createNotFoundException('Ce film n\'existe pas.');
        }

        // Récupérer les séances à venir pour ce film
        $today = new \DateTime();
        $seances = $seanceRepo->createQueryBuilder('s')
            ->andWhere('s.film = :film')
            ->andWhere('s.date_seance >= :today')
            ->setParameter('film', $film)
            ->setParameter('today', $today)
            ->leftJoin('s.salle', 'salle')
            ->addSelect('salle')
            ->leftJoin('salle.cinema', 'cinema')
            ->addSelect('cinema')
            ->orderBy('s.date_seance', 'ASC')
            ->addOrderBy('s.heure_debut', 'ASC')
            ->getQuery()
            ->getResult();

        // Récupérer les derniers avis
        $avis = $avisRepo->findRecentByFilm($film, 5);

        // Distribution des notes (pour graphique)
        $ratingDistribution = $avisRepo->getRatingDistribution($film);

        return $this->render('film/show.html.twig', [
            'film' => $film,
            'seances' => $seances,
            'avis' => $avis,
            'ratingDistribution' => $ratingDistribution,
        ]);
    }

    /**
     * Affiche un film par son TMDB ID (depuis l'API)
     */
    #[Route('/film/tmdb/{tmdbId}', name: 'app_film_show_tmdb', requirements: ['tmdbId' => '\d+'])]
    public function showByTmdbId(
        int $tmdbId,
        FilmRepository $filmRepo,
        TmdbService $tmdbService
    ): Response {
        // Vérifier si le film existe déjà en BDD
        $film = $filmRepo->findByTmdbId($tmdbId);

        if (!$film) {
            // Importer le film depuis l'API
            $movieData = $tmdbService->getMovieDetails($tmdbId);
            
            if (!$movieData) {
                throw $this->createNotFoundException('Film introuvable sur TheMovieDB.');
            }

            // TODO: Créer le film en BDD depuis les données de l'API
            // Pour l'instant, on redirige vers les détails de l'API
            
            return $this->render('film/show_tmdb.html.twig', [
                'movie' => $movieData,
                'tmdbId' => $tmdbId,
            ]);
        }

        // Rediriger vers la page normale du film
        return $this->redirectToRoute('app_film_show', ['id' => $film->getId()]);
    }

    /**
     * Recherche de films
     */
    #[Route('/films/search', name: 'app_films_search')]
    public function search(
        FilmRepository $filmRepo,
        TmdbService $tmdbService
    ): Response {
        $query = $_GET['q'] ?? '';

        if (empty($query)) {
            return $this->redirectToRoute('app_films_index');
        }

        // Rechercher d'abord dans la BDD
        $filmsLocal = $filmRepo->searchByTitre($query);

        // Rechercher aussi sur TMDB
        $filmsTmdb = $tmdbService->searchMovies($query);

        return $this->render('film/search.html.twig', [
            'query' => $query,
            'filmsLocal' => $filmsLocal,
            'filmsTmdb' => $filmsTmdb,
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
}