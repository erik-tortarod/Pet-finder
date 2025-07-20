<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250720161528 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Fix animal status enum to use correct values: LOST, FOUND, CLAIMED, FILLED';
    }

    public function up(Schema $schema): void
    {
        // Primero actualizar los valores existentes si los hay
        $this->addSql("UPDATE animals SET status = 'LOST' WHERE status = 'PERDIDO'");
        $this->addSql("UPDATE animals SET status = 'FOUND' WHERE status = 'ENCONTRADO'");
        $this->addSql("UPDATE animals SET status = 'CLAIMED' WHERE status = 'RECLAMADO'");
        $this->addSql("UPDATE animals SET status = 'FILLED' WHERE status = 'ARCHIVADO'");

        // Luego cambiar el tipo de columna al enum correcto
        $this->addSql('ALTER TABLE animals CHANGE status status ENUM(\'LOST\', \'FOUND\', \'CLAIMED\', \'FILLED\') NOT NULL COMMENT \'(DC2Type:animal_status_enum)\'');
    }

    public function down(Schema $schema): void
    {
        // Revertir los valores a español
        $this->addSql("UPDATE animals SET status = 'PERDIDO' WHERE status = 'LOST'");
        $this->addSql("UPDATE animals SET status = 'ENCONTRADO' WHERE status = 'FOUND'");
        $this->addSql("UPDATE animals SET status = 'RECLAMADO' WHERE status = 'CLAIMED'");
        $this->addSql("UPDATE animals SET status = 'ARCHIVADO' WHERE status = 'FILLED'");

        // Revertir el tipo de columna
        $this->addSql('ALTER TABLE animals CHANGE status status ENUM(\'PERDIDO\', \'ENCONTRADO\', \'ARCHIVADO\', \'RECLAMADO\') NOT NULL COMMENT \'(DC2Type:animal_status_enum)\'');
    }
}
