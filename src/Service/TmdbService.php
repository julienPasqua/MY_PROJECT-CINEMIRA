<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class TmdbService
{
    private string $apiKey;
    private HttpClientInterface $client;
    private string $baseUrl = 'https://api.themoviedb.org/3';
    private string $posterBaseUrl = 'https://image.tmdb.org/t/p/w500';

    public function __construct(HttpClientInterface $client, string $tmdbApiKey)
    {
        $this->client = $client;
        $this->apiKey = $tmdbApiKey;
    }

    /** 🔧 Méthode interne pour faire une requête TMDB */
    private function request(string $endpoint, array $params = []): array
    {
        $params['api_key']  = $this->apiKey;
        $params['language'] = 'fr-FR';

        $response = $this->client->request('GET', $this->baseUrl . $endpoint, [
            'query' => $params,
        ]);

        return $response->toArray();
    }

    /** ⭐ Films populaires */
    public function getPopularMovies(): array
    {
        return $this->request('/movie/popular', [
            'page' => 1
        ])['results'] ?? [];
    }

    /** 🔍 Recherche de films */
    public function searchMovies(string $query): array
    {
        if (strlen($query) < 2) {
            return [];
        }

        return $this->request('/search/movie', [
            'query' => $query,
            'page' => 1,
            'include_adult' => false,
        ])['results'] ?? [];
    }

    /** 🎬 Récupérer un film précis */
    public function getMovie(int $tmdbId): ?array
    {
        return $this->request('/movie/' . $tmdbId);
    }

    /** 🎬 Alias pour compatibilité (FilmController, SeanceController…) */
    public function getMovieDetails(int $tmdbId): ?array
    {
        return $this->getMovie($tmdbId);
    }

    /** 🖼 Générer l'URL de l'affiche */
    public function getPosterUrl(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        return $this->posterBaseUrl . $path;
    }
}
