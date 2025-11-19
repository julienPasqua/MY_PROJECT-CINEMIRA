<?php

namespace App\Controller;

use App\Entity\Salle;
use App\Form\SalleType;
use App\Repository\SalleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/salle')]
#[IsGranted('ROLE_ADMIN')]
class SalleController extends AbstractController
{
    /**
     * Liste toutes les salles
     */
    #[Route('', name: 'app_admin_salle_index')]
    public function index(SalleRepository $salleRepository): Response
    {
        $salles = $salleRepository->findAll();

        return $this->render('admin/salle/index.html.twig', [
            'salles' => $salles,
        ]);
    }

    /**
     * Créer une nouvelle salle
     */
    #[Route('/new', name: 'app_admin_salle_new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $salle = new Salle();
        $form = $this->createForm(SalleType::class, $salle);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($salle);
            $em->flush();

            $this->addFlash('success', '✅ Salle créée avec succès !');
            return $this->redirectToRoute('app_admin_salle_index');
        }

        return $this->render('admin/salle/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Statistiques des salles
     */
    #[Route('/stats', name: 'app_admin_salle_stats')]
    public function stats(SalleRepository $salleRepository): Response
    {
        $totalSalles = $salleRepository->count([]);
        $avgCapacity = $salleRepository->getAverageCapacity();
        $sallesParCinema = $salleRepository->getStatsByCinema();

        return $this->render('admin/salle/stats.html.twig', [
            'totalSalles' => $totalSalles,
            'avgCapacity' => $avgCapacity,
            'sallesParCinema' => $sallesParCinema,
        ]);
    }

    /**
     * Modifier une salle
     */
    #[Route('/{id}/edit', name: 'app_admin_salle_edit', requirements: ['id' => '\d+'])]
    public function edit(Request $request, Salle $salle, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(SalleType::class, $salle);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $this->addFlash('success', '✅ Salle modifiée avec succès !');

            return $this->redirectToRoute('app_admin_salle_index');
        }

        return $this->render('admin/salle/edit.html.twig', [
            'form' => $form,
            'salle' => $salle
        ]);
    }

    /**
     * Supprimer une salle
     */
    #[Route('/{id}/delete', name: 'app_admin_salle_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function delete(Request $request, Salle $salle, EntityManagerInterface $em): Response
    {
        $token = $request->request->get('_token');

        if ($this->isCsrfTokenValid('delete'.$salle->getId(), $token)) {

            $em->remove($salle);
            $em->flush();

            $this->addFlash('success', 'Salle supprimée avec succès !');
        } else {
            $this->addFlash('error', 'Token CSRF invalide !');
        }

        return $this->redirectToRoute('app_admin_salle_index');
    }


    /**
     * Afficher les détails d'une salle
     */
    #[Route('/{id}', name: 'app_admin_salle_show', requirements: ['id' => '\d+'])]
    public function show(Salle $salle): Response
    {
        return $this->render('admin/salle/show.html.twig', [
            'salle' => $salle,
        ]);
    }
}