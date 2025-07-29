<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250729164458 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add spatial indexes for latitude and longitude columns to optimize proximity searches';
    }

    public function up(Schema $schema): void
    {
        // Add indexes for latitude and longitude columns to optimize proximity searches
        $this->addSql('CREATE INDEX idx_found_animals_latitude ON found_animals (latitude)');
        $this->addSql('CREATE INDEX idx_found_animals_longitude ON found_animals (longitude)');
        $this->addSql('CREATE INDEX idx_found_animals_coordinates ON found_animals (latitude, longitude)');

        $this->addSql('CREATE INDEX idx_lost_pets_latitude ON lost_pets (latitude)');
        $this->addSql('CREATE INDEX idx_lost_pets_longitude ON lost_pets (longitude)');
        $this->addSql('CREATE INDEX idx_lost_pets_coordinates ON lost_pets (latitude, longitude)');
    }

    public function down(Schema $schema): void
    {
        // Remove the indexes
        $this->addSql('DROP INDEX idx_found_animals_latitude ON found_animals');
        $this->addSql('DROP INDEX idx_found_animals_longitude ON found_animals');
        $this->addSql('DROP INDEX idx_found_animals_coordinates ON found_animals');

        $this->addSql('DROP INDEX idx_lost_pets_latitude ON lost_pets');
        $this->addSql('DROP INDEX idx_lost_pets_longitude ON lost_pets');
        $this->addSql('DROP INDEX idx_lost_pets_coordinates ON lost_pets');
    }
}
