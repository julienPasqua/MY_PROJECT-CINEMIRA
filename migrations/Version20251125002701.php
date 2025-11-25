<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251125002701 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE reservation_siege (reservation_id INT NOT NULL, siege_id INT NOT NULL, INDEX IDX_24796450B83297E7 (reservation_id), INDEX IDX_24796450BF006E8B (siege_id), PRIMARY KEY(reservation_id, siege_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE reservation_siege ADD CONSTRAINT FK_24796450B83297E7 FOREIGN KEY (reservation_id) REFERENCES reservation (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE reservation_siege ADD CONSTRAINT FK_24796450BF006E8B FOREIGN KEY (siege_id) REFERENCES siege (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE reservation_siege DROP FOREIGN KEY FK_24796450B83297E7');
        $this->addSql('ALTER TABLE reservation_siege DROP FOREIGN KEY FK_24796450BF006E8B');
        $this->addSql('DROP TABLE reservation_siege');
    }
}
