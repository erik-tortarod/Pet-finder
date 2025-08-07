<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250807111705 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE animal_photos (id INT AUTO_INCREMENT NOT NULL, animal_id_id INT DEFAULT NULL, filename VARCHAR(255) NOT NULL, original_filename VARCHAR(255) NOT NULL, file_path VARCHAR(255) NOT NULL, file_size DOUBLE PRECISION NOT NULL, mime_type VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', is_primary TINYINT(1) DEFAULT NULL, INDEX idx_animal_id (animal_id_id), INDEX idx_is_primary (is_primary), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE animal_tags (id INT AUTO_INCREMENT NOT NULL, animal_id_id INT DEFAULT NULL, tag_id_id INT DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX idx_animal_id (animal_id_id), INDEX idx_tag_id (tag_id_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE animals (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) DEFAULT NULL, animal_type VARCHAR(255) NOT NULL, age VARCHAR(255) DEFAULT NULL, color VARCHAR(255) DEFAULT NULL, description VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', gender ENUM(\'male\', \'female\', \'dont_know\') NOT NULL COMMENT \'(DC2Type:gender_enum)\', size ENUM(\'small\', \'medium\', \'large\', \'extra_large\') NOT NULL COMMENT \'(DC2Type:size_enum)\', status ENUM(\'LOST\', \'FOUND\', \'CLAIMED\', \'FILLED\', \'ARCHIVED\', \'UNDER_PROTECTION\') NOT NULL COMMENT \'(DC2Type:animal_status_enum)\', reminder_count INT DEFAULT 0 NOT NULL, INDEX idx_animal_type (animal_type), INDEX idx_animal_gender (gender), INDEX idx_animal_size (size), INDEX idx_animal_status (status), INDEX idx_animal_created_at (created_at), INDEX idx_animal_updated_at (updated_at), INDEX idx_animal_color (color), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE found_animals (id INT AUTO_INCREMENT NOT NULL, animal_id_id INT DEFAULT NULL, user_id_id INT DEFAULT NULL, found_date DATE NOT NULL, found_time TIME DEFAULT NULL, found_zone VARCHAR(255) DEFAULT NULL, found_address VARCHAR(255) DEFAULT NULL, found_circumstances VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', additional_notes LONGTEXT DEFAULT NULL, latitude DOUBLE PRECISION DEFAULT NULL, longitude DOUBLE PRECISION DEFAULT NULL, UNIQUE INDEX UNIQ_3D76FD6B5EB747A3 (animal_id_id), INDEX idx_animal_id (animal_id_id), INDEX idx_user_id (user_id_id), INDEX idx_found_date (found_date), INDEX idx_found_zone (found_zone), INDEX idx_created_at (created_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE lost_pets (id INT AUTO_INCREMENT NOT NULL, animal_id_id INT DEFAULT NULL, user_id_id INT DEFAULT NULL, lost_date DATE NOT NULL, lost_time TIME DEFAULT NULL, lost_zone VARCHAR(255) NOT NULL, lost_address VARCHAR(255) DEFAULT NULL, lost_circumstances VARCHAR(255) DEFAULT NULL, reward_amount VARCHAR(255) DEFAULT NULL, reward_description VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', latitude DOUBLE PRECISION DEFAULT NULL, longitude DOUBLE PRECISION DEFAULT NULL, UNIQUE INDEX UNIQ_5993C515EB747A3 (animal_id_id), INDEX idx_animal_id (animal_id_id), INDEX idx_user_id (user_id_id), INDEX idx_lost_zone (lost_zone), INDEX idx_lost_date (lost_date), INDEX idx_created_at (created_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tags (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, is_active TINYINT(1) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX idx_name (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, first_name VARCHAR(255) NOT NULL, last_name VARCHAR(255) NOT NULL, phone VARCHAR(20) DEFAULT NULL, email_notifications TINYINT(1) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', last_login DATE NOT NULL, is_active TINYINT(1) NOT NULL, is_shelter TINYINT(1) NOT NULL, shelter_name VARCHAR(255) DEFAULT NULL, shelter_description VARCHAR(255) DEFAULT NULL, shelter_address VARCHAR(255) DEFAULT NULL, shelter_phone VARCHAR(255) DEFAULT NULL, shelter_website VARCHAR(255) DEFAULT NULL, shelter_facebook VARCHAR(255) DEFAULT NULL, shelter_verification_status VARCHAR(255) DEFAULT NULL, shelter_verification_date DATE DEFAULT NULL, reset_token VARCHAR(255) DEFAULT NULL, reset_token_expires_at DATETIME DEFAULT NULL, reset_token_used TINYINT(1) NOT NULL, INDEX IDX_USER_CREATED_AT (created_at), INDEX IDX_USER_IS_SHELTER (is_shelter), INDEX IDX_USER_SHELTER_VERIFICATION_STATUS (shelter_verification_status), UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE animal_photos ADD CONSTRAINT FK_B28725055EB747A3 FOREIGN KEY (animal_id_id) REFERENCES animals (id)');
        $this->addSql('ALTER TABLE animal_tags ADD CONSTRAINT FK_2891D5125EB747A3 FOREIGN KEY (animal_id_id) REFERENCES animals (id)');
        $this->addSql('ALTER TABLE animal_tags ADD CONSTRAINT FK_2891D5125DA88751 FOREIGN KEY (tag_id_id) REFERENCES tags (id)');
        $this->addSql('ALTER TABLE found_animals ADD CONSTRAINT FK_3D76FD6B5EB747A3 FOREIGN KEY (animal_id_id) REFERENCES animals (id)');
        $this->addSql('ALTER TABLE found_animals ADD CONSTRAINT FK_3D76FD6B9D86650F FOREIGN KEY (user_id_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE lost_pets ADD CONSTRAINT FK_5993C515EB747A3 FOREIGN KEY (animal_id_id) REFERENCES animals (id)');
        $this->addSql('ALTER TABLE lost_pets ADD CONSTRAINT FK_5993C519D86650F FOREIGN KEY (user_id_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE animal_photos DROP FOREIGN KEY FK_B28725055EB747A3');
        $this->addSql('ALTER TABLE animal_tags DROP FOREIGN KEY FK_2891D5125EB747A3');
        $this->addSql('ALTER TABLE animal_tags DROP FOREIGN KEY FK_2891D5125DA88751');
        $this->addSql('ALTER TABLE found_animals DROP FOREIGN KEY FK_3D76FD6B5EB747A3');
        $this->addSql('ALTER TABLE found_animals DROP FOREIGN KEY FK_3D76FD6B9D86650F');
        $this->addSql('ALTER TABLE lost_pets DROP FOREIGN KEY FK_5993C515EB747A3');
        $this->addSql('ALTER TABLE lost_pets DROP FOREIGN KEY FK_5993C519D86650F');
        $this->addSql('DROP TABLE animal_photos');
        $this->addSql('DROP TABLE animal_tags');
        $this->addSql('DROP TABLE animals');
        $this->addSql('DROP TABLE found_animals');
        $this->addSql('DROP TABLE lost_pets');
        $this->addSql('DROP TABLE tags');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
