<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250708083359 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE tags (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, is_active TINYINT(1) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX idx_name (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE INDEX idx_animal_id ON found_animals (animal_id_id)');
        $this->addSql('CREATE INDEX idx_status ON found_animals (status)');
        $this->addSql('CREATE INDEX idx_found_date ON found_animals (found_date)');
        $this->addSql('CREATE INDEX idx_found_zone ON found_animals (found_zone)');
        $this->addSql('CREATE INDEX idx_created_at ON found_animals (created_at)');
        $this->addSql('ALTER TABLE found_animals RENAME INDEX idx_3d76fd6b9d86650f TO idx_user_id');
        $this->addSql('CREATE INDEX idx_animal_id ON lost_pets (animal_id_id)');
        $this->addSql('CREATE INDEX idx_status ON lost_pets (status)');
        $this->addSql('CREATE INDEX idx_lost_zone ON lost_pets (lost_zone)');
        $this->addSql('CREATE INDEX idx_lost_date ON lost_pets (lost_date)');
        $this->addSql('CREATE INDEX idx_created_at ON lost_pets (created_at)');
        $this->addSql('ALTER TABLE lost_pets RENAME INDEX idx_5993c519d86650f TO idx_user_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE tags');
        $this->addSql('DROP INDEX idx_animal_id ON lost_pets');
        $this->addSql('DROP INDEX idx_status ON lost_pets');
        $this->addSql('DROP INDEX idx_lost_zone ON lost_pets');
        $this->addSql('DROP INDEX idx_lost_date ON lost_pets');
        $this->addSql('DROP INDEX idx_created_at ON lost_pets');
        $this->addSql('ALTER TABLE lost_pets RENAME INDEX idx_user_id TO IDX_5993C519D86650F');
        $this->addSql('DROP INDEX idx_animal_id ON found_animals');
        $this->addSql('DROP INDEX idx_status ON found_animals');
        $this->addSql('DROP INDEX idx_found_date ON found_animals');
        $this->addSql('DROP INDEX idx_found_zone ON found_animals');
        $this->addSql('DROP INDEX idx_created_at ON found_animals');
        $this->addSql('ALTER TABLE found_animals RENAME INDEX idx_user_id TO IDX_3D76FD6B9D86650F');
    }
}
