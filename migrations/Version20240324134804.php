<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240324134804 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE ligne_commande ADD id_commande INT DEFAULT NULL, ADD id_panier INT DEFAULT NULL, ADD ref_produit INT DEFAULT NULL');
        $this->addSql('ALTER TABLE ligne_commande ADD CONSTRAINT FK_3170B74B3E314AE8 FOREIGN KEY (id_commande) REFERENCES commande (id_commande)');
        $this->addSql('ALTER TABLE ligne_commande ADD CONSTRAINT FK_3170B74B2FBB81F FOREIGN KEY (id_panier) REFERENCES panier (id_panier)');
        $this->addSql('ALTER TABLE ligne_commande ADD CONSTRAINT FK_3170B74BEDB1BFF7 FOREIGN KEY (ref_produit) REFERENCES produit (ref)');
        $this->addSql('CREATE INDEX IDX_3170B74B3E314AE8 ON ligne_commande (id_commande)');
        $this->addSql('CREATE INDEX IDX_3170B74B2FBB81F ON ligne_commande (id_panier)');
        $this->addSql('CREATE INDEX IDX_3170B74BEDB1BFF7 ON ligne_commande (ref_produit)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE ligne_commande DROP FOREIGN KEY FK_3170B74B3E314AE8');
        $this->addSql('ALTER TABLE ligne_commande DROP FOREIGN KEY FK_3170B74B2FBB81F');
        $this->addSql('ALTER TABLE ligne_commande DROP FOREIGN KEY FK_3170B74BEDB1BFF7');
        $this->addSql('DROP INDEX IDX_3170B74B3E314AE8 ON ligne_commande');
        $this->addSql('DROP INDEX IDX_3170B74B2FBB81F ON ligne_commande');
        $this->addSql('DROP INDEX IDX_3170B74BEDB1BFF7 ON ligne_commande');
        $this->addSql('ALTER TABLE ligne_commande DROP id_commande, DROP id_panier, DROP ref_produit');
    }
}
