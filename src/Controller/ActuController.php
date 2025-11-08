<?php

namespace App\Controller;
use App\Service\TmdbService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class ActuController extends AbstractController
{
    #[Route('/actu/{slug}', name: 'app_actu_show')]
    public function show(string $slug): Response
    {
        
        return new Response("📰 Actualité : " . htmlspecialchars($slug));
    }

    // #[Route('/actu', name: 'app_actu_index')]
    // public function index(): Response
    // {
    //     return $this->render('actu/index.html.twig', [
    //         'news' => [
    //             ['title' => 'Première actu', 'slug' => 'premiere-actu'],
    //             ['title' => 'Deuxième actu', 'slug' => 'deuxieme-actu'],
    //         ],
    //     ]);
    // }

    #[Route('/film', name: 'app_actu_index')]
    public function index(TmdbService $tmdbService): Response
    {
        $movies = $tmdbService->getPopularMovies();

        return $this->render('actu/index.html.twig', [
            'movies' => $movies,
        ]);
    }
    
    
}