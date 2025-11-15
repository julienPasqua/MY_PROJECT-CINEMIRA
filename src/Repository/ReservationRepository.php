<?php

namespace App\Repository;

use App\Entity\Reservation;
use App\Entity\Seance;
use App\Entity\Utilisateur;
use App\Enum\ReservationStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ReservationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reservation::class);
    }

    /**
     * Trouver toutes les réservations d'une séance
     */
    public function findBySeance(Seance $seance): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.seance = :s')
            ->setParameter('s', $seance)
            ->orderBy('r.date_reservation', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouver toutes les réservations d'un utilisateur
     */
    public function findByUtilisateurs(Utilisateur $utilisateur): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.utilisateur = :u')
            ->setParameter('u', $utilisateur)
            ->orderBy('r.date_reservation', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Réservations par statut : pending, confirmed, canceled...
     */
    public function findByStatus(ReservationStatus $status): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.statut = :status')
            ->setParameter('status', $status)
            ->orderBy('r.date_reservation', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Vérifie si un code de confirmation existe déjà
     */
    public function codeExists(string $code): bool
    {
        return (bool) $this->createQueryBuilder('r')
            ->select('COUNT(r.id)')
            ->andWhere('r.code_confirmation = :c')
            ->setParameter('c', $code)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Récupère le nombre total de places réservées pour une séance
     */
    public function countPlacesForSeance(Seance $seance): int
    {
        return (int) $this->createQueryBuilder('r')
            ->select('SUM(r.nombres_places)')
            ->andWhere('r.seance = :s')
            ->setParameter('s', $seance)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Total des réservations d'un utilisateur
     */
    public function totalByUtilisateurs(Utilisateur $utilisateur): float
    {
        return (float) $this->createQueryBuilder('r')
            ->select('SUM(r.prix_total)')
            ->andWhere('r.utilisateur = :u')
            ->setParameter('u', $utilisateur)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
