<?php

namespace App\Controller;

use App\Entity\Cinema;
use App\Form\CinemaType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\CinemaRepository;
use App\Repository\SalleRepository;

class CinemaController extends AbstractController
{
    #[Route('/cinema/new', name: 'app_cinema_new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $cinema = new Cinema();
        $form = $this->createForm(CinemaType::class, $cinema);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($cinema);
            $em->flush();

            $this->addFlash('success', 'Cinéma ajouté avec succès !');

            return $this->redirectToRoute('app_cinema_list');
        }

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

    #[Route('/salles/grandes', name: 'salles_grandes')]
    public function grandesSalles(SalleRepository $salleRepository): Response
    {
        $salles = $salleRepository->findByMinCapacity(200);

        return $this->render('salle/grandes.html.twig', [
            'salles' => $salles,
        ]);
    }

    #[Route('/salles/imax', name: 'salles_imax')]
    public function sallesImax(SalleRepository $salleRepository): Response
    {
        $salles = $salleRepository->findByEquipment('IMAX');

        return $this->render('salle/imax.html.twig', [
            'salles' => $salles,
        ]);
    }

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


    /* ===================================================
       ==========   ADMIN : CINEMA CRUD   ================
       =================================================== */

    #[Route('/admin/cinema', name: 'app_admin_cinema_index')]
    public function adminIndex(CinemaRepository $cinemaRepo): Response
    {
        $cinemas = $cinemaRepo->findAll();

        return $this->render('admin/cinema/index.html.twig', [
            'cinemas' => $cinemas,
        ]);
    }


    #[Route('/admin/cinema/new', name: 'app_admin_cinema_new')]
    public function adminNew(Request $request, EntityManagerInterface $em): Response
    {
        $cinema = new Cinema();
        $form = $this->createForm(CinemaType::class, $cinema);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($cinema);
            $em->flush();

            $this->addFlash('success', 'Cinéma ajouté avec succès !');
            return $this->redirectToRoute('app_admin_cinema_index');
        }

        return $this->render('admin/cinema/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }


    #[Route('/admin/cinema/{id}/edit', name: 'app_admin_cinema_edit')]
    public function adminEdit(Request $request, Cinema $cinema, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(CinemaType::class, $cinema);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Cinéma modifié avec succès !');
            return $this->redirectToRoute('app_admin_cinema_index');
        }

        return $this->render('admin/cinema/edit.html.twig', [
            'form' => $form->createView(),
            'cinema' => $cinema,
        ]);
    }


    #[Route('/admin/cinema/{id}/delete', name: 'app_admin_cinema_delete', methods: ['POST'])]
    public function adminDelete(Request $request, Cinema $cinema, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete'.$cinema->getId(), $request->request->get('_token'))) {
            $em->remove($cinema);
            $em->flush();

            $this->addFlash('success', 'Cinéma supprimé avec succès !');
        } else {
            $this->addFlash('error', 'Erreur lors de la suppression.');
        }

        return $this->redirectToRoute('app_admin_cinema_index');
    }
}
