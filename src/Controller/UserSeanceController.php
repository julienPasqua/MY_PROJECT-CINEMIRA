<?php

namespace App\Controller;

use App\Entity\Cinema;
use App\Entity\Seance;
use APP\Entity\Siege;
use App\Entity\Reservation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/reservation')]
class UserSeanceController extends AbstractController
{
    /**
     * 🎬 Étape 1 — Choisir un cinéma
     */
    #[Route('/cinema/{tmdbId}', name: 'app_reservation_cinema')]
    public function cinema(int $tmdbId, EntityManagerInterface $em): Response
    {
        $cinemas = $em->getRepository(Cinema::class)
            ->createQueryBuilder('c')
            ->join('c.salles', 's')
            ->join('s.seances', 'se')
            ->where('se.tmdbId = :tmdb')
            ->setParameter('tmdb', $tmdbId)
            ->groupBy('c.id')
            ->getQuery()
            ->getResult();

        return $this->render('reservation/cinema.html.twig', [
            'tmdbId' => $tmdbId,
            'cinemas' => $cinemas,
        ]);
    }

    /**
     * 🎬 Étape 2 — Choisir une séance dans un cinéma
     */
    #[Route('/seances/{tmdbId}/{cinemaId}', name: 'app_reservation_seances')]
    public function seances(int $tmdbId, int $cinemaId, EntityManagerInterface $em): Response
    {
        $cinema = $em->getRepository(Cinema::class)->find($cinemaId);

        if (!$cinema) {
            throw $this->createNotFoundException("Cinéma introuvable.");
        }

        $seances = $em->getRepository(Seance::class)
            ->createQueryBuilder('s')
            ->join('s.salle', 'sa')
            ->join('sa.cinema', 'c')
            ->where('s.tmdbId = :tmdbId')
            ->andWhere('c.id = :cid')
            ->setParameter('tmdbId', $tmdbId)
            ->setParameter('cid', $cinemaId)
            ->orderBy('s.date_seance', 'ASC')
            ->addOrderBy('s.heure_debut', 'ASC')
            ->getQuery()
            ->getResult();

        return $this->render('reservation/seances.html.twig', [
            'tmdbId' => $tmdbId,
            'cinema' => $cinema,
            'seances' => $seances,
        ]);
    }


        /**
     * 🎬 Étape 3 — Choisir les sièges
     */
    #[Route('/siege/{seanceId}', name: 'app_reservation_siege')]
    public function siege(int $seanceId, EntityManagerInterface $em): Response
    {
        $seance = $em->getRepository(Seance::class)->find($seanceId);

        if (!$seance) {
            throw $this->createNotFoundException("Séance introuvable.");
        }

        // Tous les sièges de la salle
        $sieges = $em->getRepository(Siege::class)->findBy(
            ['salle' => $seance->getSalle()],
            ['numero_rangee' => 'ASC', 'numero_place' => 'ASC']
        );

        // 🟦 Sièges déjà réservés (FIX : part de Reservation, pas de Siege)
        $reserved = $em->createQueryBuilder()
            ->select('s.id')
            ->from(Reservation::class, 'r')
            ->join('r.sieges', 's')
            ->where('r.seance = :seance')
            ->setParameter('seance', $seance)
            ->getQuery()
            ->getScalarResult();

        // transforme [{id: 3}, {id: 5}] → [3, 5]
        $siegesReserves = array_column($reserved, 'id');

        return $this->render('reservation/siege.html.twig', [
            'seance' => $seance,
            'sieges' => $sieges,
            'siegesReserves' => $siegesReserves,
        ]);
    }

}
