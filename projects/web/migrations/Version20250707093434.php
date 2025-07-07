<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250707093434 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user CHANGE shelter_verificatino_status shelter_verification_status VARCHAR(255) NOT NULL');
        $this->addSql('CREATE INDEX IDX_USER_CREATED_AT ON user (created_at)');
        $this->addSql('CREATE INDEX IDX_USER_IS_SHELTER ON user (is_shelter)');
        $this->addSql('CREATE INDEX IDX_USER_SHELTER_VERIFICATION_STATUS ON user (shelter_verification_status)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX IDX_USER_CREATED_AT ON user');
        $this->addSql('DROP INDEX IDX_USER_IS_SHELTER ON user');
        $this->addSql('DROP INDEX IDX_USER_SHELTER_VERIFICATION_STATUS ON user');
        $this->addSql('ALTER TABLE user CHANGE shelter_verification_status shelter_verificatino_status VARCHAR(255) NOT NULL');
    }
}
