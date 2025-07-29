<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250729163644 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE INDEX idx_latitude ON found_animals (latitude)');
        $this->addSql('CREATE INDEX idx_longitude ON found_animals (longitude)');
        $this->addSql('CREATE INDEX idx_latitude ON lost_pets (latitude)');
        $this->addSql('CREATE INDEX idx_longitude ON lost_pets (longitude)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX idx_latitude ON lost_pets');
        $this->addSql('DROP INDEX idx_longitude ON lost_pets');
        $this->addSql('DROP INDEX idx_latitude ON found_animals');
        $this->addSql('DROP INDEX idx_longitude ON found_animals');
    }
}
