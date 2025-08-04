<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250804162314 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add UNDER_PROTECTION status to animals table enum';
    }

    public function up(Schema $schema): void
    {
        // Add UNDER_PROTECTION to the animal_status_enum
        $this->addSql("ALTER TABLE animals MODIFY COLUMN status ENUM('LOST', 'FOUND', 'CLAIMED', 'FILLED', 'ARCHIVED', 'UNDER_PROTECTION') NOT NULL COMMENT '(DC2Type:animal_status_enum)'");
    }

    public function down(Schema $schema): void
    {
        // Remove UNDER_PROTECTION from the animal_status_enum
        $this->addSql("ALTER TABLE animals MODIFY COLUMN status ENUM('LOST', 'FOUND', 'CLAIMED', 'FILLED', 'ARCHIVED') NOT NULL COMMENT '(DC2Type:animal_status_enum)'");
    }
}
