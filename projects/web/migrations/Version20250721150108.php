<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250721150108 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add ARCHIVED status to animals table enum';
    }

    public function up(Schema $schema): void
    {
        // Modificar el enum para incluir ARCHIVED
        $this->addSql("ALTER TABLE animals MODIFY COLUMN status ENUM('LOST', 'FOUND', 'CLAIMED', 'FILLED', 'ARCHIVED') NOT NULL");
    }

    public function down(Schema $schema): void
    {
        // Revertir el enum a su estado anterior
        $this->addSql("ALTER TABLE animals MODIFY COLUMN status ENUM('LOST', 'FOUND', 'CLAIMED', 'FILLED') NOT NULL");
    }
}
