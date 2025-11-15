<?php

namespace App\Repository;

use App\Entity\Genre;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Genre>
 */
class GenreRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Genre::class);
    }

    /**
     * Trouve un genre par son nom
     */
    public function findByNom(string $nom): ?Genre
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.nom = :nom')
            ->setParameter('nom', $nom)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Vérifie si un genre existe déjà
     */
    public function exists(string $nom): bool
    {
        return (bool) $this->createQueryBuilder('g')
            ->select('COUNT(g.id)')
            ->andWhere('g.nom = :nom')
            ->setParameter('nom', $nom)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Trouve tous les genres triés par nom
     */
    public function findAllOrdered(): array
    {
        return $this->createQueryBuilder('g')
            ->orderBy('g.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Recherche de genres par nom
     */
    public function search(string $query): array
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.nom LIKE :query OR g.description LIKE :query')
            ->setParameter('query', '%' . $query . '%')
            ->orderBy('g.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les genres les plus populaires (avec le plus de films)
     */
    public function findMostPopular(int $limit = 10): array
    {
        return $this->createQueryBuilder('g')
            ->select('g, COUNT(f.id) as HIDDEN nbFilms')
            ->leftJoin('g.films', 'f')
            ->groupBy('g.id')
            ->orderBy('nbFilms', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve un genre avec tous ses films chargés
     */
    public function findWithFilms(int $id): ?Genre
    {
        return $this->createQueryBuilder('g')
            ->leftJoin('g.films', 'f')
            ->addSelect('f')
            ->andWhere('g.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Compte le nombre de films par genre
     */
    public function countFilmsByGenre(Genre $genre): int
    {
        return (int) $this->createQueryBuilder('g')
            ->select('COUNT(f.id)')
            ->leftJoin('g.films', 'f')
            ->andWhere('g = :genre')
            ->setParameter('genre', $genre)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Trouve les genres qui ont au moins un film
     */
    public function findGenresWithFilms(): array
    {
        return $this->createQueryBuilder('g')
            ->innerJoin('g.films', 'f')
            ->groupBy('g.id')
            ->orderBy('g.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les genres sans films
     */
    public function findWithoutFilms(): array
    {
        return $this->createQueryBuilder('g')
            ->leftJoin('g.films', 'f')
            ->andWhere('f.id IS NULL')
            ->orderBy('g.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Compte le nombre total de genres
     */
    public function countAll(): int
    {
        return (int) $this->createQueryBuilder('g')
            ->select('COUNT(g.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Trouve ou crée un genre (utile pour l'import depuis API)
     */
    public function findOrCreate(string $nom, ?string $description = null): Genre
    {
        $genre = $this->findByNom($nom);

        if (!$genre) {
            $genre = new Genre();
            $genre->setNom($nom);
            if ($description) {
                $genre->setDescription($description);
            }
            $this->save($genre, true);
        }

        return $genre;
    }

    /**
     * Import multiple genres (utile pour TheMovieDB)
     */
    public function importGenres(array $genresData): array
    {
        $genres = [];

        foreach ($genresData as $genreData) {
            $nom = $genreData['name'] ?? $genreData['nom'];
            $description = $genreData['description'] ?? null;

            $genre = $this->findOrCreate($nom, $description);
            $genres[] = $genre;
        }

        return $genres;
    }

    /**
     * Statistiques : genres avec nombre de films
     */
    public function getStatistics(): array
    {
        return $this->createQueryBuilder('g')
            ->select('g.nom, g.id, COUNT(f.id) as nbFilms')
            ->leftJoin('g.films', 'f')
            ->groupBy('g.id')
            ->orderBy('nbFilms', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Sauvegarde un genre
     */
    public function save(Genre $genre, bool $flush = false): void
    {
        $this->getEntityManager()->persist($genre);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Supprime un genre
     */
    public function remove(Genre $genre, bool $flush = false): void
    {
        $this->getEntityManager()->remove($genre);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}