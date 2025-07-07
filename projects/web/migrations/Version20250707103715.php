<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250707103715 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE animals (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, animal_type VARCHAR(255) NOT NULL, age VARCHAR(255) DEFAULT NULL, color VARCHAR(255) DEFAULT NULL, description VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', gender ENUM(\'male\', \'female\', \'dont_know\') NOT NULL COMMENT \'(DC2Type:gender_enum)\', size ENUM(\'small\', \'medium\', \'large\', \'extra_large\') NOT NULL COMMENT \'(DC2Type:size_enum)\', INDEX idx_animal_type (animal_type), INDEX idx_animal_gender (gender), INDEX idx_animal_size (size), INDEX idx_animal_created_at (created_at), INDEX idx_animal_updated_at (updated_at), INDEX idx_animal_color (color), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, first_name VARCHAR(255) NOT NULL, last_name VARCHAR(255) NOT NULL, phone VARCHAR(20) DEFAULT NULL, email_notifications TINYINT(1) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', last_login DATE NOT NULL, is_active TINYINT(1) NOT NULL, is_shelter TINYINT(1) NOT NULL, shelter_name VARCHAR(255) DEFAULT NULL, shelter_description VARCHAR(255) DEFAULT NULL, shelter_address VARCHAR(255) DEFAULT NULL, shelter_phone VARCHAR(255) DEFAULT NULL, shelter_website VARCHAR(255) DEFAULT NULL, shelter_facebook VARCHAR(255) DEFAULT NULL, shelter_verification_status VARCHAR(255) NOT NULL, shelter_verification_date DATE NOT NULL, INDEX IDX_USER_CREATED_AT (created_at), INDEX IDX_USER_IS_SHELTER (is_shelter), INDEX IDX_USER_SHELTER_VERIFICATION_STATUS (shelter_verification_status), UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE animals');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
