<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250808165616 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        
        // D'abord, ajouter la colonne is_verified si elle n'existe pas
        $this->addSql('ALTER TABLE "user" ADD COLUMN is_verified BOOLEAN DEFAULT false');
        
        // Ensuite ajouter les colonnes de vérification
        $this->addSql('ALTER TABLE "user" ADD verification_code VARCHAR(6) DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ADD verification_code_expires_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ALTER is_verified DROP DEFAULT');
        $this->addSql('COMMENT ON COLUMN "user".verification_code_expires_at IS \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE "user" DROP verification_code');
        $this->addSql('ALTER TABLE "user" DROP verification_code_expires_at');
        $this->addSql('ALTER TABLE "user" DROP is_verified');
    }
}
