<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250901153000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Supprime et recrée la table project';
    }

    public function up(Schema $schema): void
    {
        // Supprimer la table si elle existe
        $this->addSql('DROP TABLE IF EXISTS project CASCADE');

        // Recréer la table project
        $this->addSql('CREATE TABLE project (
            id SERIAL PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            small_description VARCHAR(255) DEFAULT NULL,
            description TEXT NOT NULL,
            banner_image VARCHAR(255) DEFAULT NULL,
            images JSON DEFAULT NULL,
            link VARCHAR(255) DEFAULT NULL,
            technologies VARCHAR(2000) DEFAULT NULL,
            made_by VARCHAR(255) DEFAULT NULL,
            created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL
        )');
    }

    public function down(Schema $schema): void
    {
        // Supprimer la table project
        $this->addSql('DROP TABLE IF EXISTS project CASCADE');
    }
}
