<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251115144718 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE film (id INT AUTO_INCREMENT NOT NULL, titre VARCHAR(255) NOT NULL, synopsis LONGTEXT DEFAULT NULL, date_sortie DATE DEFAULT NULL, duree INT DEFAULT NULL, note_moyenne DOUBLE PRECISION DEFAULT NULL, poster_url VARCHAR(500) DEFAULT NULL, backdrop_url VARCHAR(500) DEFAULT NULL, tmdb_id INT NOT NULL, realisateur VARCHAR(255) DEFAULT NULL, acteur_principaux JSON DEFAULT NULL, langue_originale VARCHAR(10) DEFAULT NULL, classification VARCHAR(50) DEFAULT NULL, bande_annonce_url VARCHAR(500) DEFAULT NULL, date_creation DATETIME NOT NULL, UNIQUE INDEX UNIQ_8244BE2255BCC5E5 (tmdb_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE utilisateur CHANGE email email VARCHAR(180) NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1D1C63B3E7927C74 ON utilisateur (email)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE film');
        $this->addSql('DROP INDEX UNIQ_1D1C63B3E7927C74 ON utilisateur');
        $this->addSql('ALTER TABLE utilisateur CHANGE email email VARCHAR(100) NOT NULL');
    }
}
