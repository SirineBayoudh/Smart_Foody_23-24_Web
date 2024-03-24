<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240324130946 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE alerte (id_alerte INT AUTO_INCREMENT NOT NULL, description_alerte VARCHAR(255) DEFAULT NULL, date_alerte DATE NOT NULL, type TINYINT(1) NOT NULL, PRIMARY KEY(id_alerte)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE avis (id_avis INT AUTO_INCREMENT NOT NULL, nb_etoiles INT NOT NULL, commentaire VARCHAR(255) NOT NULL, date_avis DATE NOT NULL, signaler INT NOT NULL, PRIMARY KEY(id_avis)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE chat (id_chat INT AUTO_INCREMENT NOT NULL, question VARCHAR(255) NOT NULL, reponse VARCHAR(255) NOT NULL, PRIMARY KEY(id_chat)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE commande (id_commande INT AUTO_INCREMENT NOT NULL, date_commande DATE NOT NULL, totalecommande DOUBLE PRECISION NOT NULL, remise DOUBLE PRECISION NOT NULL, etat VARCHAR(255) NOT NULL, longitude DOUBLE PRECISION NOT NULL, latitude DOUBLE PRECISION NOT NULL, address VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id_commande)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE conseil (id_conseil INT AUTO_INCREMENT NOT NULL, statut VARCHAR(255) NOT NULL, demande VARCHAR(255) NOT NULL, reponse VARCHAR(255) NOT NULL, note INT DEFAULT NULL, date_conseil DATE NOT NULL, PRIMARY KEY(id_conseil)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ligne_commande (id_lc INT AUTO_INCREMENT NOT NULL, quantite INT NOT NULL, PRIMARY KEY(id_lc)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE objectif (id_obj INT AUTO_INCREMENT NOT NULL, libelle VARCHAR(255) NOT NULL, list_critere VARCHAR(255) NOT NULL, PRIMARY KEY(id_obj)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE panier (id_panier INT AUTO_INCREMENT NOT NULL, totale DOUBLE PRECISION NOT NULL, remise DOUBLE PRECISION NOT NULL, PRIMARY KEY(id_panier)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE produit (ref INT AUTO_INCREMENT NOT NULL, marque VARCHAR(255) NOT NULL, categorie VARCHAR(255) NOT NULL, prix DOUBLE PRECISION NOT NULL, image VARCHAR(255) NOT NULL, critere VARCHAR(255) NOT NULL, PRIMARY KEY(ref)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE reclamation (id_reclamation INT AUTO_INCREMENT NOT NULL, description VARCHAR(255) NOT NULL, titre VARCHAR(255) NOT NULL, statut VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, date_reclamation DATE NOT NULL, archive INT NOT NULL, PRIMARY KEY(id_reclamation)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE stock (id_s INT AUTO_INCREMENT NOT NULL, quantite INT NOT NULL, nb_vendu INT DEFAULT NULL, cout DOUBLE PRECISION DEFAULT NULL, nom VARCHAR(255) NOT NULL, marque VARCHAR(255) NOT NULL, date_arrivage DATE DEFAULT NULL, PRIMARY KEY(id_s)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE utilisateur (id_utilisateur INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, prenom VARCHAR(255) NOT NULL, genre VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, mot_de_passe VARCHAR(255) NOT NULL, num_tel INT NOT NULL, role VARCHAR(255) NOT NULL, matricule VARCHAR(255) NOT NULL, attestation VARCHAR(255) NOT NULL, adresse VARCHAR(255) NOT NULL, tentative INT NOT NULL, taille DOUBLE PRECISION NOT NULL, poids DOUBLE PRECISION NOT NULL, photo VARCHAR(255) NOT NULL, PRIMARY KEY(id_utilisateur)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE alerte');
        $this->addSql('DROP TABLE avis');
        $this->addSql('DROP TABLE chat');
        $this->addSql('DROP TABLE commande');
        $this->addSql('DROP TABLE conseil');
        $this->addSql('DROP TABLE ligne_commande');
        $this->addSql('DROP TABLE objectif');
        $this->addSql('DROP TABLE panier');
        $this->addSql('DROP TABLE produit');
        $this->addSql('DROP TABLE reclamation');
        $this->addSql('DROP TABLE stock');
        $this->addSql('DROP TABLE utilisateur');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
