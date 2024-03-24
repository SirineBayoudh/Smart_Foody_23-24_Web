<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240324134046 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE avis ADD id_client INT DEFAULT NULL, ADD ref_produit INT DEFAULT NULL');
        $this->addSql('ALTER TABLE avis ADD CONSTRAINT FK_8F91ABF0E173B1B8 FOREIGN KEY (id_client) REFERENCES utilisateur (id_utilisateur)');
        $this->addSql('ALTER TABLE avis ADD CONSTRAINT FK_8F91ABF0EDB1BFF7 FOREIGN KEY (ref_produit) REFERENCES produit (ref)');
        $this->addSql('CREATE INDEX IDX_8F91ABF0E173B1B8 ON avis (id_client)');
        $this->addSql('CREATE INDEX IDX_8F91ABF0EDB1BFF7 ON avis (ref_produit)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE avis DROP FOREIGN KEY FK_8F91ABF0E173B1B8');
        $this->addSql('ALTER TABLE avis DROP FOREIGN KEY FK_8F91ABF0EDB1BFF7');
        $this->addSql('DROP INDEX IDX_8F91ABF0E173B1B8 ON avis');
        $this->addSql('DROP INDEX IDX_8F91ABF0EDB1BFF7 ON avis');
        $this->addSql('ALTER TABLE avis DROP id_client, DROP ref_produit');
    }
}
