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


    #[ORM\OneToMany(mappedBy: 'ref_produit', targetEntity: Stock::class)]
    private Collection $ref_produit;

    #[ORM\OneToMany(mappedBy: 'ref_produit', targetEntity: Avis::class)]
    private Collection $avis;

    #[ORM\OneToMany(mappedBy: 'ref_produit', targetEntity: LigneCommande::class)]
    private Collection $ligneCommandes;

    #[ORM\ManyToOne(inversedBy: 'produits')]
    #[ORM\JoinColumn(name: "critere", referencedColumnName: "id_obj")]
    private ?Objectif $critere = null;



    public function __construct()
    {
        $this->ref_produit = new ArrayCollection();
        $this->avis = new ArrayCollection();
        // $this->ligneCommandes = new ArrayCollection();

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
            $refProduit->setRefProduit($this);
        }

        return $this;
    }

    public function removeRefProduit(Stock $refProduit): static
    {
        if ($this->ref_produit->removeElement($refProduit)) {
            // set the owning side to null (unless already changed)
            if ($refProduit->getRefProduit() === $this) {
                $refProduit->setRefProduit(null);
            }
        }

        return $this;
    }

    // /**
    //  * @return Collection<int, Avis>
    //  */
    public function getAvis(): Collection
    {
        return $this->avis;
    }

    public function addAvis(Avis $avi): static
    {
        if (!$this->avis->contains($avi)) {
            $this->avis->add($avi);
            $avi->setRefProduit($this);
        }

        return $this;
    }

    public function removeAvis(Avis $avi): static
    {
        if ($this->avis->removeElement($avi)) {
            // set the owning side to null (unless already changed)
            if ($avi->getRefProduit() === $this) {
                $avi->setRefProduit(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, LigneCommande>
     */
    public function getLigneCommandes(): Collection
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
    }

    public function getCritere(): ?Objectif
    {
        return $this->critere;
    }

    public function setCritere(?Objectif $critere): static
    {
        $this->critere = $critere;

        return $this;
    }


    private static $categories = [];
    public function __toString()
    {
        if ($this->ref && $this->categorie) {
            // Créer une clé unique en combinant la référence et la catégorie
            $key = $this->ref . '-' . $this->categorie;

            // Vérifier si la catégorie a déjà été rencontrée
            if (in_array($this->categorie, self::$categories)) {
                return ''; // Retourner une chaîne vide si la catégorie a déjà été rencontrée
            } else {
                // Ajouter la catégorie à la liste des catégories rencontrées
                self::$categories[] = $this->categorie;
                return $key; // Retourner la combinaison de référence et de catégorie
            }
        } elseif ($this->ref) {
            return $this->ref; // Retourner uniquement la référence si la catégorie est absente
        } elseif ($this->categorie) {
            return $this->categorie; // Retourner uniquement la catégorie si la référence est absente
        } else {
            return ''; // Retourner une chaîne vide si à la fois la référence et la catégorie sont absentes
        }
    }
}
