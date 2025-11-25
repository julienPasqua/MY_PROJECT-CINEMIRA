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

class PaymentController extends AbstractController
{
    #[Route('/paiement/create-session', name: 'app_payment_create', methods: ['POST'])]
    public function createSession(Request $request, EntityManagerInterface $em): JsonResponse
    {
        Stripe::setApiKey($_ENV['STRIPE_SECRET_KEY']);

        $seanceId = $request->get('seanceId');
        $siegesIds = explode(',', $request->get('sieges'));

        $seance = $em->getRepository(Seance::class)->find($seanceId);
        $sieges = $em->getRepository(Siege::class)->findBy(['id' => $siegesIds]);

        if (!$seance || empty($sieges)) {
            return new JsonResponse(['error' => 'Invalid data'], 400);
        }

        // 1️⃣ — Réservation PENDING avant paiement
        $reservation = new Reservation();
        $reservation->setUtilisateur($this->getUser());
        $reservation->setSeance($seance);
        $reservation->setDateReservation(new \DateTime());
        $reservation->setStatut(ReservationStatus::PENDING);
        $reservation->setPrixTotal(count($sieges) * $seance->getPrixBase());
        $reservation->setNombresPlaces(count($sieges));

        foreach ($sieges as $siege) {
            $reservation->addSiege($siege);
        }

        $em->persist($reservation);
        $em->flush();

        // 2️⃣ — Session Stripe
        $session = Session::create([
            'payment_method_types' => ['card'],

            'line_items' => [[
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => "Réservation pour " . $seance->getFilm()->getTitre(),
                    ],
                    'unit_amount' => $seance->getPrixBase() * 100,
                ],
                'quantity' => count($sieges),
            ]],

            'mode' => 'payment',

            // 🔥 3️⃣ — URLs ABSOLUES, PROPRES, FONCTIONNELLES
            'success_url' => $this->generateUrl(
                'app_payment_success_temp',
                [],
                UrlGeneratorInterface::ABSOLUTE_URL
            ),

            'cancel_url' => $this->generateUrl(
                'app_payment_cancel_temp',
                [],
                UrlGeneratorInterface::ABSOLUTE_URL
            ),
        ]);

        return new JsonResponse(['id' => $session->id]);
    }

    #[Route('/reservation/success-temp', name: 'app_payment_success_temp')]
    public function tmpSuccess(): Response
    {
        return new Response("<h1>Paiement OK ✔️</h1>");
    }

    #[Route('/reservation/cancel-temp', name: 'app_payment_cancel_temp')]
    public function tmpCancel(): Response
    {
        return new Response("<h1>Paiement annulé ❌</h1>");
    }
}
