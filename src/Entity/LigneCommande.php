<?php

namespace App\Entity;

use App\Repository\LigneCommandeRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LigneCommandeRepository::class)]
class LigneCommande
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id_lc = null;

    #[ORM\Column]
    private ?int $quantite = null;

    #[ORM\ManyToOne(inversedBy: 'ligneCommandes')]
    #[ORM\JoinColumn(name: "id_commande", referencedColumnName: "id_commande")]
    private ?commande $id_commande = null;

    #[ORM\ManyToOne(inversedBy: 'ligneCommandes')]
    #[ORM\JoinColumn(name: "id_panier", referencedColumnName: "id_panier")]
    private ?panier $id_panier = null;

    #[ORM\ManyToOne(inversedBy: 'ligneCommandes')]
    #[ORM\JoinColumn(name: "ref_produit", referencedColumnName: "ref")]
    private ?Produit $ref_produit = null;

    public function getId(): ?int
    {
        return $this->id_lc;
    }

    public function getQuantite(): ?int
    {
        return $this->quantite;
    }

    public function setQuantite(int $quantite): static
    {
        $this->quantite = $quantite;

        return $this;
    }

    public function getIdCommande(): ?commande
    {
        return $this->id_commande;
    }

    public function setIdCommande(?commande $id_commande): static
    {
        $this->id_commande = $id_commande;

        return $this;
    }

    public function getIdPanier(): ?panier
    {
        return $this->id_panier;
    }

    public function setIdPanier(?panier $id_panier): static
    {
        $this->id_panier = $id_panier;

        return $this;
    }

    public function getRefProduit(): ?Produit
    {
        return $this->ref_produit;
    }

    public function setRefProduit(?Produit $ref_produit): static
    {
        $this->ref_produit = $ref_produit;

        return $this;
    }
}
