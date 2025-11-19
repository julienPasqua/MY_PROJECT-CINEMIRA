<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class TmdbService
{
    private string $apiKey;
    private HttpClientInterface $client;

    public function __construct(HttpClientInterface $client, string $tmdbApiKey)
    {
        $this->client = $client;
        $this->apiKey = $tmdbApiKey;
    }

    private function request(string $endpoint, array $params = []): array
    {
        $params['api_key'] = $this->apiKey;
        $params['language'] = 'fr-FR';

        $response = $this->client->request('GET', 'https://api.themoviedb.org/3' . $endpoint, [
            'query' => $params,
        ]);

        return $response->toArray();
    }

    /** ⭐ Films populaires */
    public function getPopularMovies(): array
    {
        $data = $this->request('/movie/popular', [
            'page' => 1
        ]);

        return $data['results'] ?? [];
    }

    /** 🔍 Recherche */
    public function searchMovies(string $query): array
    {
        $data = $this->request('/search/movie', [
            'query' => $query,
            'page' => 1,
            'include_adult' => false,
        ]);

        return $data['results'] ?? [];
    }

    /** 🎬 Récupérer un film précis */
    public function getMovie(int $tmdbId): ?array
    {
        return $this->request('/movie/' . $tmdbId);
    }

    /** 🎬 FilmController appelle cette méthode → on l'ajoute */
    public function getMovieDetails(int $tmdbId): ?array
    {
        return $this->getMovie($tmdbId);
    }
}
