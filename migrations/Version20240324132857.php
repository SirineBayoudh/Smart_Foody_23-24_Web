<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240324132857 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE conseil ADD id_client INT DEFAULT NULL');
        $this->addSql('ALTER TABLE conseil ADD CONSTRAINT FK_3F3F0681E173B1B8 FOREIGN KEY (id_client) REFERENCES utilisateur (id_utilisateur)');
        $this->addSql('CREATE INDEX IDX_3F3F0681E173B1B8 ON conseil (id_client)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE conseil DROP FOREIGN KEY FK_3F3F0681E173B1B8');
        $this->addSql('DROP INDEX IDX_3F3F0681E173B1B8 ON conseil');
        $this->addSql('ALTER TABLE conseil DROP id_client');
    }
}
