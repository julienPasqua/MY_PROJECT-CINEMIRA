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

    public function getPopularMovies(): array
    {
        $response = $this->client->request('GET', 'https://api.themoviedb.org/3/movie/popular', [
            'query' => [
                'api_key' => $this->apiKey,
                'language' => 'fr-FR',
                'page' => 1,
            ],
        ]);

        return $response->toArray()['results'] ?? [];
    }



    public function searchMovies(string $query): array
    {
        $url = 'https://api.themoviedb.org/3/search/movie';
        $response = $this->client->request('GET', $url, [
            'query' => [
                'api_key' => $this->apiKey,
                'language' => 'fr-FR',
                'query' => $query,
                'page' => 1,
                'include_adult' => false,
            ],
        ]);

        return $response->toArray()['results'] ?? [];
    }
}
