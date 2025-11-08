<?php

namespace App\Controller;

use App\Entity\Cinema;
use App\Form\CinemaType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\SalleRepository;

class CinemaController extends AbstractController
{
    #[Route('/cinema/new', name: 'app_cinema_new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        // 1️⃣ Créer un nouvel objet Cinema vide
        $cinema = new Cinema();

        // 2️⃣ Créer le formulaire à partir du CinemaType
        $form = $this->createForm(CinemaType::class, $cinema);

        // 3️⃣ Récupérer les données de la requête (soumission du formulaire)
        $form->handleRequest($request);

        // 4️⃣ Si le formulaire est valide → on sauvegarde
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($cinema);
            $em->flush();

            $this->addFlash('success', 'Cinéma ajouté avec succès !');

            // Redirection vers une page (par ex. la liste)
            return $this->redirectToRoute('app_cinema_list');
        }

        // 5️⃣ Afficher le formulaire dans la vue
        return $this->render('cinema/cinema.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/cinema', name: 'app_cinema_list')]
    public function list(EntityManagerInterface $em): Response
    {
        $cinemas = $em->getRepository(Cinema::class)->findAll();

        return $this->render('cinema/index.html.twig', [
            'cinemas' => $cinemas,
        ]);
    }

        // Lister toutes les salles d'un cinéma
    #[Route('/cinema/{id}/salles', name: 'cinema_salles')]
    public function salles(int $id, SalleRepository $salleRepository): Response
    {
        $salles = $salleRepository->findByCinema($id);
        $totalCapacity = $salleRepository->getTotalCapacityByCinema($id);
        
        return $this->render('cinema/salles.html.twig', [
            'salles' => $salles,
            'totalCapacity' => $totalCapacity,
        ]);
    }

    // Trouver les grandes salles
    #[Route('/salles/grandes', name: 'salles_grandes')]
    public function grandesSalles(SalleRepository $salleRepository): Response
    {
        $salles = $salleRepository->findByMinCapacity(200);
        
        return $this->render('salle/grandes.html.twig', [
            'salles' => $salles,
        ]);
    }

    // Salles IMAX
    #[Route('/salles/imax', name: 'salles_imax')]
    public function sallesImax(SalleRepository $salleRepository): Response
    {
        $salles = $salleRepository->findByEquipment('IMAX');
        
        return $this->render('salle/imax.html.twig', [
            'salles' => $salles,
        ]);
    }

    // Statistiques
    #[Route('/admin/stats-salles', name: 'admin_stats_salles')]
    public function stats(SalleRepository $salleRepository): Response
    {
        $stats = $salleRepository->getStatsByCinema();
        $avgCapacity = $salleRepository->getAverageCapacity();
        
        return $this->render('admin/stats.html.twig', [
            'stats' => $stats,
            'avgCapacity' => $avgCapacity,
        ]);
    }
}
