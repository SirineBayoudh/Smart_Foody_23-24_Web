<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240324135013 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE panier ADD id_client INT DEFAULT NULL');
        $this->addSql('ALTER TABLE panier ADD CONSTRAINT FK_24CC0DF2E173B1B8 FOREIGN KEY (id_client) REFERENCES utilisateur (id_utilisateur)');
        $this->addSql('CREATE INDEX IDX_24CC0DF2E173B1B8 ON panier (id_client)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE panier DROP FOREIGN KEY FK_24CC0DF2E173B1B8');
        $this->addSql('DROP INDEX IDX_24CC0DF2E173B1B8 ON panier');
        $this->addSql('ALTER TABLE panier DROP id_client');
    }
}
