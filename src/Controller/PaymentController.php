<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Entity\Seance;
use App\Entity\Siege;
use App\Enum\ReservationStatus;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use App\Service\PdfService;

class PaymentController extends AbstractController
{
    #[Route('/paiement/create-session', name: 'app_payment_create', methods: ['POST'])]
    public function createSession(Request $request, EntityManagerInterface $em): JsonResponse
    {
        Stripe::setApiKey($_ENV['STRIPE_SECRET_KEY']);

        $seanceId  = $request->request->get('seanceId');
        $siegesRaw = $request->request->get('sieges');

        $siegesIds = array_filter(explode(',', $siegesRaw));

        $seance = $em->getRepository(Seance::class)->find($seanceId);
        $sieges = $em->getRepository(Siege::class)->findBy(['id' => $siegesIds]);

        if (!$seance || empty($sieges)) {
            return new JsonResponse(['error' => 'Invalid data'], 400);
        }

        // On stocke temporairement les infos dans la SESSION
        $sessionSymfony = $request->getSession();
        $sessionSymfony->set('seanceId', $seanceId);
        $sessionSymfony->set('siegesIds', $siegesIds);

        $session = Session::create([
            'payment_method_types' => ['card'],

            'line_items' => [[
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => "Réservation : " . $seance->getFilm()->getTitre(),
                    ],
                    'unit_amount' => $seance->getPrixBase() * 100,
                ],
                'quantity' => count($sieges),
            ]],

            'mode' => 'payment',

            'success_url' => $this->generateUrl(
                'app_reservation_confirmer_from_stripe',
                [],
                UrlGeneratorInterface::ABSOLUTE_URL
            ),


            'cancel_url' => $this->generateUrl(
                'home.index',
                [],
                UrlGeneratorInterface::ABSOLUTE_URL
            ),
        ]);

        return new JsonResponse(['id' => $session->id]);
    }


    #[Route('/paiement/success', name: 'app_payment_success', methods: ['GET'])]
    public function success(
        Request $request,
        EntityManagerInterface $em,
        PdfService $pdfService
    ): Response {

        $this->denyAccessUnlessGranted('ROLE_USER');

        $session = $request->getSession();
        $seanceId = $session->get('seanceId');
        $siegesIds = $session->get('siegesIds');

        if (!$seanceId || empty($siegesIds)) {
            throw $this->createNotFoundException("Session Stripe vide.");
        }

        $seance = $em->getRepository(Seance::class)->find($seanceId);
        $sieges = $em->getRepository(Siege::class)->findBy(['id' => $siegesIds]);

        // Création de la réservation
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

        // Génération du PDF
        $html = $this->renderView('reservation/ticket.html.twig', [
            'reservation' => $reservation
        ]);
        $pdfService->generatePdf($html, 'ticket_' . $reservation->getId() . '.pdf');

        // Nettoyage session Stripe
        $session->remove('seanceId');
        $session->remove('siegesIds');

        return $this->redirectToRoute('app_reservation_success', [
            'id' => $reservation->getId()
        ]);
    }
}
