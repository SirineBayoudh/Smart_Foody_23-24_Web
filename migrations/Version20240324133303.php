<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240324133303 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE stock ADD ref_produit INT DEFAULT NULL');
        $this->addSql('ALTER TABLE stock ADD CONSTRAINT FK_4B365660EDB1BFF7 FOREIGN KEY (ref_produit) REFERENCES produit (ref)');
        $this->addSql('CREATE INDEX IDX_4B365660EDB1BFF7 ON stock (ref_produit)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE stock DROP FOREIGN KEY FK_4B365660EDB1BFF7');
        $this->addSql('DROP INDEX IDX_4B365660EDB1BFF7 ON stock');
        $this->addSql('ALTER TABLE stock DROP ref_produit');
    }
}
