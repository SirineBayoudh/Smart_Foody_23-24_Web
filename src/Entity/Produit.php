<?php

namespace App\Entity;

use App\Repository\ProduitRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ProduitRepository::class)]
class Produit
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $ref = null;

    #[ORM\Column(length: 255 , nullable: true)]
    #[Assert\NotBlank(message:'La marque est obligatoire')]
    private ?string $marque = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'La catégorie est obligatoire')]
    private ?string $categorie = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: 'Le prix est obligatoire')]
    #[Assert\Regex(
        pattern: '/^\d+(\.\d+)?$/',
        message: 'Le prix doit être un nombre valide'
    )]
    #[Assert\Type(
        type: 'float',
        message: 'Le prix doit être un nombre'
    )]
    #[Assert\Range(min: 0.0, max: 9999.9, minMessage: 'Le prix ne peut pas être négatif')]
    private ?float $prix = null;

    #[ORM\Column(length: 255,nullable: true)]
    private ?string $image = null;


    #[ORM\OneToMany(mappedBy: 'produit', targetEntity: Stock::class)]
    private Collection $ref_produit;

    #[ORM\OneToMany(mappedBy: 'ref_produit', targetEntity: Avis::class)]
    private Collection $avis;

    //#[ORM\OneToMany(mappedBy: 'ref_produit', targetEntity: LigneCommande::class)]
    //private Collection $ligneCommandes;

    #[ORM\ManyToOne(inversedBy: 'produits')]
    #[ORM\JoinColumn(name: "critere", referencedColumnName: "id_obj")]
    #[Assert\NotBlank(message: 'Le critere est obligatoire')]
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
