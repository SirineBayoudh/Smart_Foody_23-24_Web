<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240329144119 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE alerte ADD id_stock INT DEFAULT NULL');
        $this->addSql('ALTER TABLE alerte ADD CONSTRAINT FK_3AE753AA5B31750 FOREIGN KEY (id_stock) REFERENCES stock (id_s)');
        $this->addSql('CREATE INDEX IDX_3AE753AA5B31750 ON alerte (id_stock)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE alerte DROP FOREIGN KEY FK_3AE753AA5B31750');
        $this->addSql('DROP INDEX IDX_3AE753AA5B31750 ON alerte');
        $this->addSql('ALTER TABLE alerte DROP id_stock');
    }
}
