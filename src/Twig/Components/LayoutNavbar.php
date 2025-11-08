<?php

namespace App\Twig\Components;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class LayoutNavbar
{
    public string $brandName;
    public array $links = [
        ['path' => 'home.index', 'label' => 'home'],
        ['path' => '/movies', 'label' => 'Films'],
        ['path' => '/series', 'label' => 'Séries'],
        ['path' => 'app_actu_index', 'label' => 'Actualités'],
        ['path' => '/about', 'label' => 'À propos'],
    ];

    public array $news = [
        ['title' => 'Première actu', 'slug' => 'premiere-actu'],
        ['title' => 'Deuxième actu', 'slug' => 'deuxieme-actu'],
    ];
}
