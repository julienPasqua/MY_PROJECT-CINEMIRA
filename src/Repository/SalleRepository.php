<?php

namespace App\Repository;

use App\Entity\Salle;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Salle>
 */
class SalleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Salle::class);
    }

    /**
     * Trouve toutes les salles triées par nom
     * @return Salle[]
     */
    public function findAllOrderedByName(): array
    {
        return $this->createQueryBuilder('s')
            ->orderBy('s.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve toutes les salles d'un cinéma spécifique
     * @return Salle[]
     */
    public function findByCinema(int $cinemaId): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.cinema = :cinemaId')
            ->setParameter('cinemaId', $cinemaId)
            ->orderBy('s.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les salles avec une capacité minimale
     * @return Salle[]
     */
    public function findByMinCapacity(int $minCapacity): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.capacity >= :minCapacity')
            ->setParameter('minCapacity', $minCapacity)
            ->orderBy('s.capacity', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les salles avec une capacité entre min et max
     * @return Salle[]
     */
    public function findByCapacityRange(int $minCapacity, int $maxCapacity): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.capacity >= :min')
            ->andWhere('s.capacity <= :max')
            ->setParameter('min', $minCapacity)
            ->setParameter('max', $maxCapacity)
            ->orderBy('s.capacity', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les plus grandes salles (par capacité)
     * @return Salle[]
     */
    public function findLargest(int $limit = 10): array
    {
        return $this->createQueryBuilder('s')
            ->orderBy('s.capacity', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les salles par type d'équipement (3D, IMAX, Dolby...)
     * @return Salle[]
     */
    public function findByEquipment(string $equipment): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.equipment LIKE :equipment')
            ->setParameter('equipment', '%' . $equipment . '%')
            ->orderBy('s.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Recherche des salles par nom
     * @return Salle[]
     */
    public function searchByName(string $query): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.name LIKE :query')
            ->setParameter('query', '%' . $query . '%')
            ->orderBy('s.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Compte le nombre total de salles
     */
    public function countAll(): int
    {
        return $this->createQueryBuilder('s')
            ->select('COUNT(s.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Compte le nombre de salles par cinéma
     */
    public function countByCinema(int $cinemaId): int
    {
        return $this->createQueryBuilder('s')
            ->select('COUNT(s.id)')
            ->andWhere('s.cinema = :cinemaId')
            ->setParameter('cinemaId', $cinemaId)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Calcule la capacité totale d'un cinéma
     */
    public function getTotalCapacityByCinema(int $cinemaId): int
    {
        $result = $this->createQueryBuilder('s')
            ->select('SUM(s.capacity)')
            ->andWhere('s.cinema = :cinemaId')
            ->setParameter('cinemaId', $cinemaId)
            ->getQuery()
            ->getSingleScalarResult();

        return (int) ($result ?? 0);
    }

    /**
     * Calcule la capacité moyenne des salles
     */
    public function getAverageCapacity(): float
    {
        $result = $this->createQueryBuilder('s')
            ->select('AVG(s.capacity)')
            ->getQuery()
            ->getSingleScalarResult();

        return (float) ($result ?? 0);
    }

    /**
     * Trouve les salles disponibles (sans séance en cours) à une date donnée
     * @return Salle[]
     */
    public function findAvailableAtDateTime(\DateTimeInterface $dateTime): array
    {
        return $this->createQueryBuilder('s')
            ->leftJoin('s.seances', 'se')
            ->andWhere('se.startTime IS NULL OR se.startTime > :dateTime OR se.endTime < :dateTime')
            ->setParameter('dateTime', $dateTime)
            ->orderBy('s.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les salles avec leurs cinémas (évite les requêtes N+1)
     * @return Salle[]
     */
    public function findAllWithCinema(): array
    {
        return $this->createQueryBuilder('s')
            ->leftJoin('s.cinema', 'c')
            ->addSelect('c')
            ->orderBy('c.name', 'ASC')
            ->addOrderBy('s.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve une salle par son nom et son cinéma
     */
    public function findOneByNameAndCinema(string $name, int $cinemaId): ?Salle
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.name = :name')
            ->andWhere('s.cinema = :cinemaId')
            ->setParameter('name', $name)
            ->setParameter('cinemaId', $cinemaId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Trouve les statistiques des salles par cinéma
     * @return array [cinemaId => ['count' => X, 'totalCapacity' => Y]]
     */
    public function getStatsByCinema(): array
    {
        $results = $this->createQueryBuilder('s')
            ->select('IDENTITY(s.cinema) as cinemaId')
            ->addSelect('COUNT(s.id) as count')
            ->addSelect('SUM(s.capacity) as totalCapacity')
            ->addSelect('AVG(s.capacity) as avgCapacity')
            ->groupBy('s.cinema')
            ->getQuery()
            ->getResult();

        $stats = [];
        foreach ($results as $result) {
            $stats[$result['cinemaId']] = [
                'count' => (int) $result['count'],
                'totalCapacity' => (int) $result['totalCapacity'],
                'avgCapacity' => (float) $result['avgCapacity'],
            ];
        }

        return $stats;
    }
}