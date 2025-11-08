<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251107150604 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE siege (id INT AUTO_INCREMENT NOT NULL, salle_id INT NOT NULL, numero_rangee VARCHAR(5) NOT NULL, numero_place INT NOT NULL, type VARCHAR(255) NOT NULL, prix_supplement NUMERIC(15, 2) DEFAULT NULL, INDEX IDX_6706B4F7DC304035 (salle_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE siege ADD CONSTRAINT FK_6706B4F7DC304035 FOREIGN KEY (salle_id) REFERENCES salle (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE siege DROP FOREIGN KEY FK_6706B4F7DC304035');
        $this->addSql('DROP TABLE siege');
    }
}
