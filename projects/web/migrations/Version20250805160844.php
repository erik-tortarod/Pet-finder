<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250805160844 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE animal_reminder DROP FOREIGN KEY FK_86B4499D5EB747A3');
        $this->addSql('ALTER TABLE animal_reminder DROP FOREIGN KEY FK_86B4499D9D86650F');
        $this->addSql('DROP TABLE animal_reminder');
        $this->addSql('ALTER TABLE animals ADD reminder_count INT DEFAULT 0 NOT NULL');
        $this->addSql('DROP INDEX idx_longitude ON found_animals');
        $this->addSql('DROP INDEX idx_latitude ON found_animals');
        $this->addSql('DROP INDEX idx_found_animals_coordinates ON found_animals');
        $this->addSql('DROP INDEX idx_found_animals_latitude ON found_animals');
        $this->addSql('DROP INDEX idx_found_animals_longitude ON found_animals');
        $this->addSql('DROP INDEX idx_latitude ON lost_pets');
        $this->addSql('DROP INDEX idx_longitude ON lost_pets');
        $this->addSql('DROP INDEX idx_lost_pets_coordinates ON lost_pets');
        $this->addSql('DROP INDEX idx_lost_pets_latitude ON lost_pets');
        $this->addSql('DROP INDEX idx_lost_pets_longitude ON lost_pets');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE animal_reminder (id INT AUTO_INCREMENT NOT NULL, animal_id_id INT NOT NULL, user_id_id INT NOT NULL, next_reminder_date DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', status VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX idx_animal_id (animal_id_id), INDEX idx_next_reminder (next_reminder_date), INDEX idx_status (status), INDEX idx_user_id (user_id_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE animal_reminder ADD CONSTRAINT FK_86B4499D5EB747A3 FOREIGN KEY (animal_id_id) REFERENCES animals (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE animal_reminder ADD CONSTRAINT FK_86B4499D9D86650F FOREIGN KEY (user_id_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE animals DROP reminder_count');
        $this->addSql('CREATE INDEX idx_longitude ON found_animals (longitude)');
        $this->addSql('CREATE INDEX idx_latitude ON found_animals (latitude)');
        $this->addSql('CREATE INDEX idx_found_animals_coordinates ON found_animals (latitude, longitude)');
        $this->addSql('CREATE INDEX idx_found_animals_latitude ON found_animals (latitude)');
        $this->addSql('CREATE INDEX idx_found_animals_longitude ON found_animals (longitude)');
        $this->addSql('CREATE INDEX idx_latitude ON lost_pets (latitude)');
        $this->addSql('CREATE INDEX idx_longitude ON lost_pets (longitude)');
        $this->addSql('CREATE INDEX idx_lost_pets_coordinates ON lost_pets (latitude, longitude)');
        $this->addSql('CREATE INDEX idx_lost_pets_latitude ON lost_pets (latitude)');
        $this->addSql('CREATE INDEX idx_lost_pets_longitude ON lost_pets (longitude)');
    }
}
