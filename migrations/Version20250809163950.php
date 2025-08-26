<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250809163950 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Recréation propre de la table project pour stocker toutes les informations des projets';
    }

    public function up(Schema $schema): void
    {
        // Supprime la table si elle existe déjà (attention : perte de données !)
        if ($schema->hasTable('project')) {
            $this->addSql('DROP TABLE project');
        }

        $this->addSql('CREATE TABLE project (
            id SERIAL PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            image VARCHAR(255) DEFAULT NULL,
            description TEXT DEFAULT NULL,
            technologies VARCHAR(500) DEFAULT NULL,
            features TEXT DEFAULT NULL,
            role TEXT DEFAULT NULL,
            github VARCHAR(255) DEFAULT NULL,
            link VARCHAR(255) DEFAULT NULL,
            created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL
        )');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE project');
    }
}
