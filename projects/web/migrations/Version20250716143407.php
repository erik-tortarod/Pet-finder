<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250716143407 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Update animal status values to new format: LOST, FOUND, CLAIMED, FILLED';
    }

    public function up(Schema $schema): void
    {
        // Update existing status values to new format
        $this->addSql("UPDATE animals SET status = 'LOST' WHERE status = 'PERDIDO'");
        $this->addSql("UPDATE animals SET status = 'FOUND' WHERE status = 'ENCONTRADO'");
        // Set any other values to FOUND as default
        $this->addSql("UPDATE animals SET status = 'FOUND' WHERE status NOT IN ('LOST', 'FOUND', 'CLAIMED', 'FILLED')");
    }

    public function down(Schema $schema): void
    {
        // Revert status values to old format
        $this->addSql("UPDATE animals SET status = 'PERDIDO' WHERE status = 'LOST'");
        $this->addSql("UPDATE animals SET status = 'ENCONTRADO' WHERE status = 'FOUND'");
        // Set any other values to ENCONTRADO as default
        $this->addSql("UPDATE animals SET status = 'ENCONTRADO' WHERE status NOT IN ('PERDIDO', 'ENCONTRADO', 'RECLAMADO', 'ARCHIVADO')");
    }
}
