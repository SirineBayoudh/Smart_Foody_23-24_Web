<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240324132242 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE utilisateur ADD objectif INT DEFAULT NULL');
        $this->addSql('ALTER TABLE utilisateur ADD CONSTRAINT FK_1D1C63B3E2F86851 FOREIGN KEY (objectif) REFERENCES objectif (id_obj)');
        $this->addSql('CREATE INDEX IDX_1D1C63B3E2F86851 ON utilisateur (objectif)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE utilisateur DROP FOREIGN KEY FK_1D1C63B3E2F86851');
        $this->addSql('DROP INDEX IDX_1D1C63B3E2F86851 ON utilisateur');
        $this->addSql('ALTER TABLE utilisateur DROP objectif');
    }
}
