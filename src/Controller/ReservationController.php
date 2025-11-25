<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Entity\Seance;
use App\Entity\Siege;
use App\Enum\ReservationStatus;
use App\Service\PdfService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/reservation')]
class ReservationController extends AbstractController
{
    /* ============================================================
     *   1️⃣ — LISTE DES RÉSERVATIONS
     * ============================================================ */
    #[Route('/mes-reservations', name: 'app_reservation_index')]
    public function index(EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $reservations = $em->getRepository(Reservation::class)->findBy(
            ['utilisateur' => $this->getUser()],
            ['dateReservation' => 'DESC']
        );

        return $this->render('reservation/index.html.twig', [
            'reservations' => $reservations
        ]);
    }


    /* ============================================================
     *   2️⃣ — VOIR UNE RÉSERVATION
     * ============================================================ */
    #[Route('/show/{id}', name: 'app_reservation_show')]
    public function show(Reservation $reservation): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        if ($reservation->getUtilisateur() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('reservation/show.html.twig', [
            'reservation' => $reservation
        ]);
    }


    /* ============================================================
     *   3️⃣ — ANNULER (GET)
     * ============================================================ */
    #[Route('/{id}/annuler', name: 'app_reservation_annuler')]
    public function annuler(Reservation $reservation, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        if ($reservation->getUtilisateur() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $reservation->setStatut(ReservationStatus::ANNULEE);
        $em->flush();

        $this->addFlash('success', 'La réservation a bien été annulée.');
        return $this->redirectToRoute('app_reservation_index');
    }


    /* ============================================================
     *   4️⃣ — ANNULATION (POST)
     * ============================================================ */
    #[Route('/cancel/{id}', name: 'app_reservation_cancel', methods: ['POST'])]
    public function cancel(Request $request, Reservation $reservation, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        if ($reservation->getUtilisateur() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        if ($this->isCsrfTokenValid('cancel_resa_' . $reservation->getId(), $request->get('_token'))) {
            $reservation->setStatut(ReservationStatus::ANNULEE);
            $em->flush();
            $this->addFlash('success', 'Votre réservation a bien été annulée.');
        }

        return $this->redirectToRoute('app_reservation_index');
    }


    /* ============================================================
     *   🎫 5️⃣ — Récapitulatif
     * ============================================================ */
    #[Route('/recap', name: 'app_reservation_recap_get', methods: ['GET'])]
    public function recapGet(): Response
    {
        return $this->redirectToRoute('home.index'); 
    }



    #[Route('/recap', name: 'app_reservation_recap', methods: ['POST'])]
    public function recap(Request $request, EntityManagerInterface $em): Response
    {
        $seanceId = $request->get('seanceId');
        $siegesIds = explode(',', $request->get('sieges'));

        $seance = $em->getRepository(Seance::class)->find($seanceId);
        $siegesChoisis = $em->getRepository(Siege::class)->findBy(['id' => $siegesIds]);

        $film = $seance->getFilm();
        $cinema = $seance->getSalle()->getCinema();
        $prixUnitaire = $seance->getPrixBase();
        $total = count($siegesChoisis) * $prixUnitaire;

        return $this->render('reservation/recap.html.twig', [
            'film' => $film,
            'cinema' => $cinema,
            'seance' => $seance,
            'siegesChoisis' => $siegesChoisis,
            'siegesIds' => $siegesIds,
            'total' => $total,
            'stripePublicKey' => $this->getParameter('stripe_public_key'),
        ]);
    }


    /* ============================================================
     *   6️⃣ — CONFIRMATION + PDF + SUCCESS
     * ============================================================ */
    #[Route('/confirmer', name: 'app_reservation_confirmer', methods: ['POST'])]
    public function confirmer(
        Request $request,
        EntityManagerInterface $em,
        PdfService $pdfService
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $seanceId = $request->get('seanceId');
        $siegesIds = explode(',', $request->get('sieges'));

        $seance = $em->getRepository(Seance::class)->find($seanceId);
        $sieges = $em->getRepository(Siege::class)->findBy(['id' => $siegesIds]);

        if (!$seance || empty($sieges)) {
            $this->addFlash('danger', 'Impossible de valider la réservation.');
            return $this->redirectToRoute('app_reservation_index');
        }

        // ⭐ Création réservation
        $reservation = new Reservation();
        $reservation->setUtilisateur($this->getUser());
        $reservation->setSeance($seance);
        $reservation->setDateReservation(new \DateTime());
        $reservation->setStatut(ReservationStatus::CONFIRMEE);
        $reservation->setPrixTotal(count($sieges) * $seance->getPrixBase());
        $reservation->setNombresPlaces(count($sieges));

        foreach ($sieges as $siege) {
            $reservation->addSiege($siege);
        }

        $em->persist($reservation);
        $em->flush();

        // ⭐ Génération PDF
        $html = $this->renderView('reservation/ticket.html.twig', [
            'reservation' => $reservation
        ]);

        $filename = 'ticket_' . $reservation->getId() . '.pdf';
        $pdfService->generatePdf($html, $filename);

        // ⭐ Success
        return $this->redirectToRoute('app_reservation_success', [
            'id' => $reservation->getId()
        ]);
    }


    #[Route('/success/{id}', name: 'app_reservation_success')]
    public function success(Reservation $reservation): Response
    {
        return $this->render('reservation/success.html.twig', [
            'reservation' => $reservation
        ]);
    }
}
