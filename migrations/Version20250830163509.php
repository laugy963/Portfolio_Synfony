<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250830163509 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // $this->addSql('ALTER TABLE project ADD made_by VARCHAR(255) DEFAULT NULL');
        // $this->addSql('ALTER TABLE project DROP made');
    }

    public function down(Schema $schema): void
    {
        // $this->addSql('ALTER TABLE project ADD made BOOLEAN DEFAULT false NOT NULL');
        // $this->addSql('ALTER TABLE project DROP made_by');
    }
}
       // $this->addSql('ALTER TABLE project DROP made_by');
