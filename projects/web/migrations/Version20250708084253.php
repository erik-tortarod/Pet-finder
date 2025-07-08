<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250708084253 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE animal_photos (id INT AUTO_INCREMENT NOT NULL, animal_id_id INT DEFAULT NULL, filename VARCHAR(255) NOT NULL, original_filename VARCHAR(255) NOT NULL, file_path VARCHAR(255) NOT NULL, file_size DOUBLE PRECISION NOT NULL, mime_type VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', is_primary TINYINT(1) DEFAULT NULL, INDEX idx_animal_id (animal_id_id), INDEX idx_is_primary (is_primary), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE animal_photos ADD CONSTRAINT FK_B28725055EB747A3 FOREIGN KEY (animal_id_id) REFERENCES animals (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE animal_photos DROP FOREIGN KEY FK_B28725055EB747A3');
        $this->addSql('DROP TABLE animal_photos');
    }
}
