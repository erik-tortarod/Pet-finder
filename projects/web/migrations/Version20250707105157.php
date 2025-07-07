<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250707105157 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE lost_pets (id INT AUTO_INCREMENT NOT NULL, animal_id_id INT DEFAULT NULL, user_id_id INT DEFAULT NULL, lost_date DATE NOT NULL, lost_time TIME DEFAULT NULL, lost_zone VARCHAR(255) NOT NULL, lost_address VARCHAR(255) DEFAULT NULL, lost_circumstances VARCHAR(255) DEFAULT NULL, reward_amount VARCHAR(255) DEFAULT NULL, reward_description VARCHAR(255) DEFAULT NULL, status ENUM(\'active\', \'found\', \'paused\', \'filled\') NOT NULL COMMENT \'(DC2Type:status_enum)\', created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_5993C515EB747A3 (animal_id_id), INDEX IDX_5993C519D86650F (user_id_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE lost_pets ADD CONSTRAINT FK_5993C515EB747A3 FOREIGN KEY (animal_id_id) REFERENCES animals (id)');
        $this->addSql('ALTER TABLE lost_pets ADD CONSTRAINT FK_5993C519D86650F FOREIGN KEY (user_id_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE lost_pets DROP FOREIGN KEY FK_5993C515EB747A3');
        $this->addSql('ALTER TABLE lost_pets DROP FOREIGN KEY FK_5993C519D86650F');
        $this->addSql('DROP TABLE lost_pets');
    }
}
