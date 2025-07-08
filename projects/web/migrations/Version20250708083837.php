<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250708083837 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE animal_tags (id INT AUTO_INCREMENT NOT NULL, animal_id_id INT DEFAULT NULL, tag_id_id INT DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX idx_animal_id (animal_id_id), INDEX idx_tag_id (tag_id_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE animal_tags ADD CONSTRAINT FK_2891D5125EB747A3 FOREIGN KEY (animal_id_id) REFERENCES animals (id)');
        $this->addSql('ALTER TABLE animal_tags ADD CONSTRAINT FK_2891D5125DA88751 FOREIGN KEY (tag_id_id) REFERENCES tags (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE animal_tags DROP FOREIGN KEY FK_2891D5125EB747A3');
        $this->addSql('ALTER TABLE animal_tags DROP FOREIGN KEY FK_2891D5125DA88751');
        $this->addSql('DROP TABLE animal_tags');
    }
}
