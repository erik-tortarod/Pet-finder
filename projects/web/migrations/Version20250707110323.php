<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250707110323 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE found_animals (id INT AUTO_INCREMENT NOT NULL, animal_id_id INT DEFAULT NULL, user_id_id INT DEFAULT NULL, found_date DATE NOT NULL, found_time TIME DEFAULT NULL, found_zone VARCHAR(255) DEFAULT NULL, found_address VARCHAR(255) DEFAULT NULL, found_circumstances VARCHAR(255) DEFAULT NULL, status ENUM(\'active\', \'claimed\', \'paused\', \'filled\', \'under_protection\') NOT NULL COMMENT \'(DC2Type:found_animal_status_enum)\', created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', additional_notes LONGTEXT DEFAULT NULL, UNIQUE INDEX UNIQ_3D76FD6B5EB747A3 (animal_id_id), INDEX IDX_3D76FD6B9D86650F (user_id_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE found_animals ADD CONSTRAINT FK_3D76FD6B5EB747A3 FOREIGN KEY (animal_id_id) REFERENCES animals (id)');
        $this->addSql('ALTER TABLE found_animals ADD CONSTRAINT FK_3D76FD6B9D86650F FOREIGN KEY (user_id_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE found_animals DROP FOREIGN KEY FK_3D76FD6B5EB747A3');
        $this->addSql('ALTER TABLE found_animals DROP FOREIGN KEY FK_3D76FD6B9D86650F');
        $this->addSql('DROP TABLE found_animals');
    }
}
