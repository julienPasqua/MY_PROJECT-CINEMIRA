<?php

namespace App\Repository;

use App\Entity\Film;
use App\Entity\Genre;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Film>
 */
class FilmRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Film::class);
    }

    /**
     * Trouve un film par son ID TheMovieDB
     */
    public function findByTmdbId(int $tmdbId): ?Film
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.tmdb_id = :tmdb')
            ->setParameter('tmdb', $tmdbId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Vérifie si un film existe déjà via son tmdb_id
     */
    public function existsByTmdbId(int $tmdbId): bool
    {
        return (bool) $this->createQueryBuilder('f')
            ->select('COUNT(f.id)')
            ->andWhere('f.tmdb_id = :tmdb')
            ->setParameter('tmdb', $tmdbId)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Trouve tous les films à l'affiche (ayant des séances à venir)
     */
    public function findFilmsALaffiche(): array
    {
        $today = new \DateTime();

        return $this->createQueryBuilder('f')
            ->innerJoin('f.seances', 's')
            ->andWhere('s.date_seance >= :today')
            ->setParameter('today', $today)
            ->groupBy('f.id')
            ->orderBy('f.date_creation', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les films les mieux notés
     */
    public function findTopRated(int $limit = 10): array
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.note_moyenne IS NOT NULL')
            ->orderBy('f.note_moyenne', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les films récents (sortis dans les X derniers mois)
     */
    public function findRecent(int $months = 6, int $limit = 20): array
    {
        $date = new \DateTime("-{$months} months");

        return $this->createQueryBuilder('f')
            ->andWhere('f.date_sortie >= :date')
            ->setParameter('date', $date)
            ->orderBy('f.date_sortie', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Recherche de films par titre
     */
    public function searchByTitre(string $query): array
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.titre LIKE :query')
            ->setParameter('query', '%' . $query . '%')
            ->orderBy('f.titre', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Recherche avancée (titre, réalisateur, acteurs)
     */
    public function searchAdvanced(string $query): array
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.titre LIKE :query OR f.realisateur LIKE :query OR f.acteur_principaux LIKE :query')
            ->setParameter('query', '%' . $query . '%')
            ->orderBy('f.note_moyenne', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les films par genre
     */
    public function findByGenre(Genre $genre): array
    {
        return $this->createQueryBuilder('f')
            ->innerJoin('f.genres', 'g')
            ->andWhere('g = :genre')
            ->setParameter('genre', $genre)
            ->orderBy('f.note_moyenne', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les films par réalisateur
     */
    public function findByRealisateur(string $realisateur): array
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.realisateur = :real')
            ->setParameter('real', $realisateur)
            ->orderBy('f.date_sortie', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les films avec le plus de séances
     */
    public function findMostScreened(int $limit = 10): array
    {
        $today = new \DateTime();

        return $this->createQueryBuilder('f')
            ->select('f, COUNT(s.id) as HIDDEN nbSeances')
            ->leftJoin('f.seances', 's')
            ->andWhere('s.date_seance >= :today')
            ->setParameter('today', $today)
            ->groupBy('f.id')
            ->orderBy('nbSeances', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les films les plus réservés
     */
    public function findMostBooked(int $limit = 10): array
    {
        return $this->createQueryBuilder('f')
            ->select('f, COUNT(r.id) as HIDDEN nbReservations')
            ->leftJoin('f.seances', 's')
            ->leftJoin('s.reservations', 'r')
            ->groupBy('f.id')
            ->orderBy('nbReservations', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve un film avec toutes ses relations chargées (évite N+1)
     */
    public function findWithRelations(int $id): ?Film
    {
        return $this->createQueryBuilder('f')
            ->leftJoin('f.genres', 'g')
            ->addSelect('g')
            ->leftJoin('f.seances', 's')
            ->addSelect('s')
            ->leftJoin('f.avis', 'a')
            ->addSelect('a')
            ->andWhere('f.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Trouve les films par année de sortie
     */
    public function findByYear(int $year): array
    {
        return $this->createQueryBuilder('f')
            ->andWhere('YEAR(f.date_sortie) = :year')
            ->setParameter('year', $year)
            ->orderBy('f.date_sortie', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les films par langue originale
     */
    public function findByLangue(string $langue): array
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.langue_originale = :langue')
            ->setParameter('langue', $langue)
            ->orderBy('f.note_moyenne', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les films par classification d'âge
     */
    public function findByClassification(string $classification): array
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.classification = :class')
            ->setParameter('class', $classification)
            ->orderBy('f.note_moyenne', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Compte le nombre total de films
     */
    public function countAll(): int
    {
        return (int) $this->createQueryBuilder('f')
            ->select('COUNT(f.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Calcule et met à jour la note moyenne d'un film basée sur les avis
     */
    public function updateNoteMoyenne(Film $film): void
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        
        $moyenne = $qb->select('AVG(a.note)')
            ->from('App\Entity\Avis', 'a')
            ->andWhere('a.film = :film')
            ->setParameter('film', $film)
            ->getQuery()
            ->getSingleScalarResult();

        $film->setNoteMoyenne($moyenne ? (float) $moyenne : null);
        
        $this->getEntityManager()->persist($film);
        $this->getEntityManager()->flush();
    }

    /**
     * Trouve le film du mois (le mieux noté récemment)
     */
    public function findFilmDuMois(): ?Film
    {
        $date = new \DateTime('-1 month');

        return $this->createQueryBuilder('f')
            ->andWhere('f.date_creation >= :date OR f.date_sortie >= :date')
            ->setParameter('date', $date)
            ->orderBy('f.note_moyenne', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Sauvegarde un film
     */
    public function save(Film $film, bool $flush = false): void
    {
        $this->getEntityManager()->persist($film);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Supprime un film
     */
    public function remove(Film $film, bool $flush = false): void
    {
        $this->getEntityManager()->remove($film);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}