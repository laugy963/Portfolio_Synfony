<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250830155033 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE project ADD small_description VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE project ADD banner_image VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE project ADD images JSON DEFAULT NULL');
        $this->addSql('ALTER TABLE project DROP image');
        $this->addSql('ALTER TABLE project DROP github');
        $this->addSql('ALTER TABLE project DROP created_at');
        $this->addSql('ALTER TABLE project RENAME COLUMN features TO made');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE project ADD image VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE project ADD github VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE project ADD created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL');
        $this->addSql('ALTER TABLE project DROP small_description');
        $this->addSql('ALTER TABLE project DROP banner_image');
        $this->addSql('ALTER TABLE project DROP images');
        $this->addSql('ALTER TABLE project RENAME COLUMN made TO features');
    }
}
