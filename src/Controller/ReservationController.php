<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Entity\Seance;
use App\Entity\Siege;
use App\Entity\Film;
use App\Service\TmdbService;
use App\Enum\ReservationStatus;
use App\Repository\FilmRepository;
use App\Service\PdfService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/reservation')]
class ReservationController extends AbstractController
{
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


    #[Route('/annuler/{id}', name: 'app_reservation_annuler')]
    public function annuler(Reservation $reservation, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        if ($reservation->getUtilisateur() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $reservation->setStatut(ReservationStatus::ANNULEE);
        $em->flush();

        return $this->redirectToRoute('app_reservation_index');
    }


    #[Route('/cancel/{id}', name: 'app_reservation_cancel', methods: ['POST'])]
    public function cancelPost(Request $request, Reservation $reservation, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        if ($reservation->getUtilisateur() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        if ($this->isCsrfTokenValid('cancel_resa_' . $reservation->getId(), $request->get('_token'))) {
            $reservation->setStatut(ReservationStatus::ANNULEE);
            $em->flush();
        }

        return $this->redirectToRoute('app_reservation_index');
    }
 


    #[Route('/recap', name: 'app_reservation_recap_get', methods: ['GET'])]
    public function recapGet(): Response
    {
        return $this->redirectToRoute('home.index');
    }


    #[Route('/recap', name: 'app_reservation_recap', methods: ['POST'])]
    public function recap(Request $request, EntityManagerInterface $em): Response
    {
        $seanceId = $request->request->get('seanceId');
        $siegesId = explode(',', $request->request->get('sieges'));
 
       


        $seance = $em->getRepository(Seance::class)->find($seanceId);
        $sieges = $em->getRepository(Siege::class)->findBy(['id' => $siegesId]);

        if (!$seance || empty($sieges)) {
            throw $this->createNotFoundException("Impossible de générer le récapitulatif.");
        }

        $film = $seance->getFilm();
        $cinema = $seance->getSalle()->getCinema();

        $total = count($sieges) * $seance->getPrixBase();

        return $this->render('reservation/recap.html.twig', [
            'film' => $film,
            'cinema' => $cinema,
            'seance' => $seance,
            'siegesChoisis' => $sieges,
            'siegesIds' => $siegesId,
            'total' => $total,
            'stripePublicKey' => $_ENV['STRIPE_PUBLIC_KEY']
        ]);
    }

     #[Route('/confirmer', name: 'app_reservation_confirmer', methods: ['POST'])]
    public function confirmer(
        Request $request,
        EntityManagerInterface $em,
        PdfService $pdfService
    ): Response {

        $this->denyAccessUnlessGranted('ROLE_USER');

        $seanceId = $request->request->get('seanceId');
        $siegesIds = explode(',', $request->request->get('sieges'));

        $seance = $em->getRepository(Seance::class)->find($seanceId);
        $sieges = $em->getRepository(Siege::class)->findBy(['id' => $siegesIds]);

        if (!$seance || empty($sieges)) {
            $this->addFlash('danger', 'Impossible de valider la réservation.');
            return $this->redirectToRoute('home.index');
        }

        // 🟦 Création de la réservation
        $reservation = new Reservation();
        $reservation->setUtilisateur($this->getUser());
        $reservation->setSeance($seance);
        $reservation->setDateReservation(new \DateTime());
        $reservation->setStatut(ReservationStatus::CONFIRMEE);
        $reservation->setPrixTotal(count($sieges) * $seance->getPrixBase());
        $reservation->setNombresPlaces(count($sieges));
        $reservation->setCodeConfirmation(uniqid('RESA-'));

        foreach ($sieges as $siege) {
            $reservation->addSiege($siege);
        }

        $em->persist($reservation);
        $em->flush();

        // 🟦 Génération du ticket PDF
        $html = $this->renderView('reservation/ticket.html.twig', [
            'reservation' => $reservation
        ]);

        $filename = 'ticket_' . $reservation->getId() . '.pdf';
        $pdfService->generatePdf($html, $filename);

        // 🟦 Redirection page success
        return $this->redirectToRoute('app_reservation_success', [
            'id' => $reservation->getId()
        ]);
    }



    #[Route('/success/{id}', name: 'app_reservation_success')]
    public function success(Reservation $reservation): Response
    {
        return $this->render('reservation/success.html.twig', [
            'reservation' => $reservation,
            'ticketPath' => '/tickets/ticket_' . $reservation->getId() . '.pdf'
        ]);
    }



    #[Route('/cinema/{tmdbId}', name: 'app_reservation_cinema')]
    public function choisirCinema(
        int $tmdbId,
        FilmRepository $filmRepo,
        TmdbService $tmdbService,
        EntityManagerInterface $em
    ): Response {

        $film = $filmRepo->findByTmdbId($tmdbId);

        if (!$film) {
            $movieData = $tmdbService->getMovieDetails($tmdbId);

            $film = new Film();
            $film->setTitre($movieData['title']);
            $film->setTmdbId($tmdbId);
            $film->setSynopsis($movieData['overview']);
            $film->setPosterUrl($movieData['poster_path']);
            $film->setBackdropUrl($movieData['backdrop_path']);
            $film->setDateSortie(new \DateTime($movieData['release_date'] ?? 'now'));

            $em->persist($film);
            $em->flush();
        }

        return $this->render('reservation/cinemas.html.twig', [
            'film' => $film,
        ]);
    }
    #[Route('/confirmer-stripe', name: 'app_reservation_confirmer_from_stripe', methods:['GET'])]
    public function confirmerFromStripe(
        Request $request,
        EntityManagerInterface $em,
        PdfService $pdfService
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');

        // 🔹 On récupère les données sauvegardées dans la session
        $session = $request->getSession();
        $seanceId = $session->get('seanceId');
        $siegesIds = $session->get('siegesIds');

        if (!$seanceId || empty($siegesIds)) {
            throw $this->createNotFoundException("Impossible de finaliser la réservation.");
        }

        $seance = $em->getRepository(Seance::class)->find($seanceId);
        $sieges = $em->getRepository(Siege::class)->findBy(['id' => $siegesIds]);

        if (!$seance || empty($sieges)) {
            throw $this->createNotFoundException("Erreur interne.");
        }

        // 🔹 CREATION DE LA RÉSERVATION
        $reservation = new Reservation();
        $reservation->setUtilisateur($this->getUser());
        $reservation->setSeance($seance);
        $reservation->setDateReservation(new \DateTime());
        $reservation->setStatut(ReservationStatus::CONFIRMEE);
        $reservation->setPrixTotal(count($sieges) * $seance->getPrixBase());
        $reservation->setNombresPlaces(count($sieges));
        $reservation->setCodeConfirmation(uniqid('RESA-'));

        foreach ($sieges as $siege) {
            $reservation->addSiege($siege);
        }

        $em->persist($reservation);
        $em->flush();

        // 🔹 Génération du PDF
        $html = $this->renderView('reservation/ticket.html.twig', [
            'reservation' => $reservation
        ]);

        $pdfFile = 'ticket_' . $reservation->getId() . '.pdf';
        $pdfService->generatePdf($html, $pdfFile);

        // 🔹 Redirection vers la page de succès
        return $this->redirectToRoute('app_reservation_success', [
            'id' => $reservation->getId()
        ]);
    }

     #[Route('/seance/{id}/sieges', name: 'app_reservation_sieges')]
    public function choisirSieges(
        Seance $seance,
        EntityManagerInterface $em
    ): Response {

        // 🔹 Récupérer TOUS les sièges de la salle
        $sieges = $em->getRepository(Siege::class)->findBy([
            'salle' => $seance->getSalle()
        ]);

        // 🔹 Récupérer les sièges déjà réservés pour cette séance
        $resiRepo = $em->getRepository(Reservation::class);
        $siegesReserves = $resiRepo->createQueryBuilder('r')
            ->select('s.id')
            ->join('r.sieges', 's')
            ->where('r.seance = :seance')
            ->setParameter('seance', $seance)
            ->getQuery()
            ->getScalarResult();

        // Retourne un tableau d'IDs uniquement
        $idsReserves = array_column($siegesReserves, 'id');

        return $this->render('reservation/siege.html.twig', [
            'seance' => $seance,
            'sieges' => $sieges,
            'siegesReserves' => $idsReserves
        ]);
    }


}
