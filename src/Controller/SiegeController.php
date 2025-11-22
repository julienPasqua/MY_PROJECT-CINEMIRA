<?php

namespace App\Controller;

use App\Entity\Siege;
use App\Entity\Salle;
use App\Enum\TypeSiege;
use App\Form\SiegeType;
use App\Form\GenerateSiegesType;
use App\Repository\SiegeRepository;
use App\Repository\SalleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/siege')]
class SiegeController extends AbstractController
{
    #[Route('/', name: 'app_admin_siege_index', methods: ['GET'])]
    public function index(SiegeRepository $siegeRepository, SalleRepository $salleRepository): Response
    {
        return $this->render('admin/siege/index.html.twig', [
            'sieges' => $siegeRepository->findAll(),
            'salles' => $salleRepository->findAll(),
        ]);
    }

    #[Route('/salle/{id}', name: 'app_admin_siege_by_salle', methods: ['GET'])]
    public function bySalle(Salle $salle): Response
    {
        return $this->render('admin/siege/by_salle.html.twig', [
            'salle' => $salle,
            'sieges' => $salle->getSieges(),
        ]);
    }

    #[Route('/new', name: 'app_admin_siege_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $siege = new Siege();
        $form = $this->createForm(SiegeType::class, $siege);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($siege);
            $em->flush();

            $this->addFlash('success', 'Siège créé avec succès !');
            return $this->redirectToRoute('app_admin_siege_index');
        }

        return $this->render('admin/siege/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/generate', name: 'app_admin_siege_generate', methods: ['GET', 'POST'])]
    public function generate(Request $request, EntityManagerInterface $em, SalleRepository $salleRepository): Response
    {
        $form = $this->createForm(GenerateSiegesType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $salle = $data['salle'];
            $nbRangees = $data['nb_rangees'];
            $nbPlacesParRangee = $data['nb_places_par_rangee'];
            $type = $data['type'];
            $prixSupplement = $data['prix_supplement'];

            $count = 0;
            for ($r = 1; $r <= $nbRangees; $r++) {
                $rangee = chr(64 + $r); // 1=A, 2=B, 3=C...
                
                for ($p = 1; $p <= $nbPlacesParRangee; $p++) {
                    $siege = new Siege();
                    $siege->setSalle($salle);
                    $siege->setNumeroRangee($rangee);
                    $siege->setNumeroPlace($p);
                    $siege->setType($type);
                    $siege->setPrixSupplement($prixSupplement);
                    
                    $em->persist($siege);
                    $count++;
                }
            }

            $em->flush();

            $this->addFlash('success', "$count sièges créés pour la salle {$salle->getNom()} !");
            return $this->redirectToRoute('app_admin_siege_by_salle', ['id' => $salle->getId()]);
        }

        return $this->render('admin/siege/generate.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_siege_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Siege $siege, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(SiegeType::class, $siege);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $this->addFlash('success', 'Siège modifié avec succès !');
            return $this->redirectToRoute('app_admin_siege_by_salle', ['id' => $siege->getSalle()->getId()]);
        }

        return $this->render('admin/siege/edit.html.twig', [
            'form' => $form->createView(),
            'siege' => $siege,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_admin_siege_delete', methods: ['POST'])]
    public function delete(Request $request, Siege $siege, EntityManagerInterface $em): Response
    {
        $salleId = $siege->getSalle()->getId();

        if ($this->isCsrfTokenValid('delete' . $siege->getId(), $request->request->get('_token'))) {
            $em->remove($siege);
            $em->flush();
            $this->addFlash('success', 'Siège supprimé !');
        }

        return $this->redirectToRoute('app_admin_siege_by_salle', ['id' => $salleId]);
    }

    #[Route('/salle/{id}/delete-all', name: 'app_admin_siege_delete_all', methods: ['POST'])]
    public function deleteAll(Request $request, Salle $salle, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete_all' . $salle->getId(), $request->request->get('_token'))) {
            foreach ($salle->getSieges() as $siege) {
                $em->remove($siege);
            }
            $em->flush();
            $this->addFlash('success', 'Tous les sièges de la salle ont été supprimés !');
        }

        return $this->redirectToRoute('app_admin_siege_by_salle', ['id' => $salle->getId()]);
    }
}