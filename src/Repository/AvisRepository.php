<?php

namespace App\Repository;

use App\Entity\Avis;
use App\Entity\Film;
use App\Entity\Utilisateur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Avis>
 */
class AvisRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Avis::class);
    }

    /**
     * Trouve tous les avis d'un film
     */
    public function findByFilm(Film $film): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.film = :film')
            ->setParameter('film', $film)
            ->orderBy('a.date_creation', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve tous les avis d'un utilisateur
     */
    public function findByUtilisateur(Utilisateur $utilisateur): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.utilisateur = :user')
            ->setParameter('user', $utilisateur)
            ->orderBy('a.date_creation', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les derniers avis (pour page d'accueil ou flux)
     */
    public function findLatest(int $limit = 10): array
    {
        return $this->createQueryBuilder('a')
            ->leftJoin('a.film', 'f')
            ->addSelect('f')
            ->leftJoin('a.utilisateur', 'u')
            ->addSelect('u')
            ->orderBy('a.date_creation', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les avis les mieux notés
     */
    public function findTopRated(int $limit = 10): array
    {
        return $this->createQueryBuilder('a')
            ->leftJoin('a.film', 'f')
            ->addSelect('f')
            ->leftJoin('a.utilisateur', 'u')
            ->addSelect('u')
            ->orderBy('a.note', 'DESC')
            ->addOrderBy('a.date_creation', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Vérifie si un utilisateur a déjà laissé un avis sur un film
     */
    public function userHasReviewed(Utilisateur $utilisateur, Film $film): bool
    {
        return (bool) $this->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->andWhere('a.utilisateur = :user')
            ->andWhere('a.film = :film')
            ->setParameter('user', $utilisateur)
            ->setParameter('film', $film)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Trouve l'avis d'un utilisateur sur un film spécifique
     */
    public function findUserReview(Utilisateur $utilisateur, Film $film): ?Avis
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.utilisateur = :user')
            ->andWhere('a.film = :film')
            ->setParameter('user', $utilisateur)
            ->setParameter('film', $film)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Calcule la note moyenne d'un film
     */
    public function getAverageRatingForFilm(Film $film): ?float
    {
        $result = $this->createQueryBuilder('a')
            ->select('AVG(a.note)')
            ->andWhere('a.film = :film')
            ->setParameter('film', $film)
            ->getQuery()
            ->getSingleScalarResult();

        return $result ? (float) $result : null;
    }

    /**
     * Compte le nombre d'avis pour un film
     */
    public function countByFilm(Film $film): int
    {
        return (int) $this->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->andWhere('a.film = :film')
            ->setParameter('film', $film)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Compte le nombre d'avis d'un utilisateur
     */
    public function countByUtilisateur(Utilisateur $utilisateur): int
    {
        return (int) $this->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->andWhere('a.utilisateur = :user')
            ->setParameter('user', $utilisateur)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Trouve les avis par note (filtre)
     */
    public function findByNote(int $note): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.note = :note')
            ->setParameter('note', $note)
            ->orderBy('a.date_creation', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les avis avec une note minimale
     */
    public function findByMinNote(int $minNote): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.note >= :min')
            ->setParameter('min', $minNote)
            ->orderBy('a.note', 'DESC')
            ->addOrderBy('a.date_creation', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Recherche dans le contenu des avis
     */
    public function search(string $query): array
    {
        return $this->createQueryBuilder('a')
            ->leftJoin('a.film', 'f')
            ->addSelect('f')
            ->leftJoin('a.utilisateur', 'u')
            ->addSelect('u')
            ->andWhere('a.contenu LIKE :query')
            ->setParameter('query', '%' . $query . '%')
            ->orderBy('a.date_creation', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les avis récents pour un film (avec utilisateurs chargés)
     */
    public function findRecentByFilm(Film $film, int $limit = 5): array
    {
        return $this->createQueryBuilder('a')
            ->leftJoin('a.utilisateur', 'u')
            ->addSelect('u')
            ->andWhere('a.film = :film')
            ->setParameter('film', $film)
            ->orderBy('a.date_creation', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Statistiques : distribution des notes pour un film
     */
    public function getRatingDistribution(Film $film): array
    {
        return $this->createQueryBuilder('a')
            ->select('a.note, COUNT(a.id) as count')
            ->andWhere('a.film = :film')
            ->setParameter('film', $film)
            ->groupBy('a.note')
            ->orderBy('a.note', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les utilisateurs les plus actifs (plus d'avis)
     */
    public function findMostActiveReviewers(int $limit = 10): array
    {
        return $this->createQueryBuilder('a')
            ->select('u, COUNT(a.id) as HIDDEN nbAvis')
            ->leftJoin('a.utilisateur', 'u')
            ->groupBy('u.id')
            ->orderBy('nbAvis', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les avis créés après une certaine date
     */
    public function findCreatedAfter(\DateTimeInterface $date): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.date_creation >= :date')
            ->setParameter('date', $date)
            ->orderBy('a.date_creation', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Compte les nouveaux avis sur une période (en jours)
     */
    public function countNewAvis(int $days = 30): int
    {
        $date = new \DateTime("-{$days} days");

        return (int) $this->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->andWhere('a.date_creation >= :date')
            ->setParameter('date', $date)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Compte le nombre total d'avis
     */
    public function countAll(): int
    {
        return (int) $this->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Sauvegarde un avis et met à jour la note moyenne du film
     */
    public function saveAndUpdateFilmRating(Avis $avis, bool $flush = true): void
    {
        $this->getEntityManager()->persist($avis);

        if ($flush) {
            $this->getEntityManager()->flush();

            // Mettre à jour la note moyenne du film
            $film = $avis->getFilm();
            $moyenne = $this->getAverageRatingForFilm($film);
            $film->setNoteMoyenne($moyenne);

            $this->getEntityManager()->persist($film);
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Sauvegarde un avis
     */
    public function save(Avis $avis, bool $flush = false): void
    {
        $this->getEntityManager()->persist($avis);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Supprime un avis et met à jour la note du film
     */
    public function removeAndUpdateFilmRating(Avis $avis, bool $flush = true): void
    {
        $film = $avis->getFilm();

        $this->getEntityManager()->remove($avis);

        if ($flush) {
            $this->getEntityManager()->flush();

            // Recalculer la note moyenne
            $moyenne = $this->getAverageRatingForFilm($film);
            $film->setNoteMoyenne($moyenne);

            $this->getEntityManager()->persist($film);
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Supprime un avis
     */
    public function remove(Avis $avis, bool $flush = false): void
    {
        $this->getEntityManager()->remove($avis);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}