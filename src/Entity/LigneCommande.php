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

    #[ORM\Column(length: 255)]
    private ?string $quantite = null;




  
    #[ORM\ManyToOne(inversedBy: 'panier')]
    #[ORM\JoinColumn(name: "id_panier", referencedColumnName: "id_panier")]
    private ?Panier $panier = null;




      
      #[ORM\ManyToOne(inversedBy: 'produit')]
      #[ORM\JoinColumn(name: "ref_produit", referencedColumnName: "ref")]
      private ?Produit $produit = null;

   
      #[ORM\ManyToOne(inversedBy: 'commande')]
      #[ORM\JoinColumn(name: "id_commande", referencedColumnName: "id")]
      private ?Commande $commande = null;
  
  
      
      public function getCommande(): ?Commande
      {
          return $this->commande;
      }
  
      public function setCommande(?Commande $commande): self
      {
          $this->commande = $commande;
          return $this;
      }
    
      public function getProduit(): ?Produit
      {
          return $this->produit;
      }
  
      public function setProduit(?Produit $produit): self
      {
          $this->produit = $produit;
          return $this;
      }

    public function getId_lc(): ?int
    {
        return $this->id_lc;
    }

    public function getQuantite(): ?string
    {
        return $this->quantite;
    }

    public function setQuantite(string $quantite): self
    {
        $this->quantite = $quantite;
        return $this;
    }

   /*    public function getCommande(): ?Commande
    {
        return $this->commande;
    }

    public function setCommande(?Commande $commande): self
    {
        $this->commande = $commande;
        return $this;
    }*/


    public function getPanier(): ?Panier
    {
        return $this->panier;
    }

    public function setPanier(?Panier $panier): self
    {
        $this->panier = $panier;
        return $this;
    }

 







  
}
