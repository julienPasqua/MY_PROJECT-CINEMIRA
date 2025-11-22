<?php

namespace App\Controller;

use App\Entity\Cinema;
use App\Entity\Salle;
use App\Form\CinemaType;
use App\Form\SalleType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\CinemaRepository;
use App\Repository\SalleRepository;
use App\Repository\SeanceRepository;
use App\Repository\ReservationRepository;
use App\Repository\SiegeRepository;

#[Route('/admin')]
class AdminController extends AbstractController
{
    #[Route('/', name: 'app_admin_dashboard')]
    public function dashboard(CinemaRepository $cinemaRepo, SalleRepository $salleRepo, SeanceRepository $seancesRepo, ReservationRepository $reservationRepo, SiegeRepository $siegeRepo   ): Response
    {
        $cinemaCount = $cinemaRepo->count([]);
        $salleCount = $salleRepo->count([]);
        $seancesCount = $seancesRepo->count([]);
        $reservationCount = $reservationRepo->count([]);
        $siegeCount = $siegeRepo->count([]);

        return $this->render('admin/dashboard/index.html.twig', [
            'cinemaCount' => $cinemaCount,
            'salleCount' => $salleCount,
            'seanceCount' => $seancesCount,
            'reservationCount' => $reservationCount,
            'siegeCount' => $siegeCount,
        ]);
    }

    #[Route('/cinema/new', name: 'app_admin_cinema_new')]
    public function newCinema(Request $request, EntityManagerInterface $em): Response
    {
        $cinema = new Cinema();
        $form = $this->createForm(CinemaType::class, $cinema);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($cinema);
            $em->flush();

            $this->addFlash('success', '✅Cinéma ajouté avec succès !');

            return $this->redirectToRoute('app_admin_dashboard');
        }

        return $this->render('admin/cinema/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    #[Route('/salle/new', name: 'app_admin_salle_new')]
    public function newSalle(Request $request, EntityManagerInterface $em): Response
    {
        $salle = new Salle();
        $form = $this->createForm(SalleType::class, $salle);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($salle);
            $em->flush();

            $this->addFlash('success', '✅ Salle ajoutée avec succès !');

            return $this->redirectToRoute('app_admin_dashboard');
        }

        return $this->render('admin/salle/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }


}




   