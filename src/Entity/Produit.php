<?php

namespace App\Entity;

use App\Repository\ProduitRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProduitRepository::class)]
class Produit
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $ref = null;

    #[ORM\Column(length: 255)]
    private ?string $marque = null;

    #[ORM\Column(length: 255)]
    private ?string $categorie = null;

    #[ORM\Column]
    private ?float $prix = null;

    #[ORM\Column(length: 255)]
    private ?string $image = null;


    #[ORM\OneToMany(mappedBy: 'produit', targetEntity: Stock::class)]
    private Collection $ref_produit;

    #[ORM\OneToMany(mappedBy: 'ref_produit', targetEntity: Avis::class)]
    private Collection $avis;

    //#[ORM\OneToMany(mappedBy: 'ref_produit', targetEntity: LigneCommande::class)]
    //private Collection $ligneCommandes;

    #[ORM\ManyToOne(inversedBy: 'produits')]
    #[ORM\JoinColumn(name: "critere", referencedColumnName: "id_obj")]
    private ?Objectif $critere = null;

    

    public function __construct()
    {
        $this->ref_produit = new ArrayCollection();
        $this->avis = new ArrayCollection();
        //$this->ligneCommandes = new ArrayCollection();

    }

    public function getRef(): ?int
    {
        return $this->ref;
    }

    public function getMarque(): ?string
    {
        return $this->marque;
    }

    public function setMarque(string $marque): static
    {
        $this->marque = $marque;

        return $this;
    }

    public function getCategorie(): ?string
    {
        return $this->categorie;
    }

    public function setCategorie(string $categorie): static
    {
        $this->categorie = $categorie;

        return $this;
    }

    public function getPrix(): ?float
    {
        return $this->prix;
    }

    public function setPrix(float $prix): static
    {
        $this->prix = $prix;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(string $image): static
    {
        $this->image = $image;

        return $this;
    }


    /**
     * @return Collection<int, Stock>
     */
    public function getRefProduit(): Collection
    {
        return $this->ref_produit;
    }

    public function addRefProduit(Stock $refProduit): static
    {
        if (!$this->ref_produit->contains($refProduit)) {
            $this->ref_produit->add($refProduit);
            $refProduit->setProduit($this);
        }

        return $this;
    }

    public function removeRefProduit(Stock $refProduit): static
    {
        if ($this->ref_produit->removeElement($refProduit)) {
            // set the owning side to null (unless already changed)
            if ($refProduit->getProduit() === $this) {
                $refProduit->setProduit(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Avis>
     */
    public function getAvis(): Collection
    {
        return $this->avis;
    }

    public function addAvi(Avis $avi): static
    {
        if (!$this->avis->contains($avi)) {
            $this->avis->add($avi);
            $avi->setRefProduit($this);
        }

        return $this;
    }

    public function removeAvi(Avis $avi): static
    {
        if ($this->avis->removeElement($avi)) {
            // set the owning side to null (unless already changed)
            if ($avi->getRefProduit() === $this) {
                $avi->setRefProduit(null);
            }
        }

        return $this;
    }

    /*/**
     * @return Collection<int, LigneCommande>
     */
    /*public function getLigneCommandes(): Collection
    {
        return $this->ligneCommandes;
    }

    public function addLigneCommande(LigneCommande $ligneCommande): static
    {
        if (!$this->ligneCommandes->contains($ligneCommande)) {
            $this->ligneCommandes->add($ligneCommande);
            $ligneCommande->setRefProduit($this);
        }

        return $this;
    }

    public function removeLigneCommande(LigneCommande $ligneCommande): static
    {
        if ($this->ligneCommandes->removeElement($ligneCommande)) {
            // set the owning side to null (unless already changed)
            if ($ligneCommande->getRefProduit() === $this) {
                $ligneCommande->setRefProduit(null);
            }
        }

        return $this;
    }*/

    public function getCritere(): ?Objectif
    {
        return $this->critere;
    }

    public function setCritere(?Objectif $critere): static
    {
        $this->critere = $critere;

        return $this;
    }

    
}
