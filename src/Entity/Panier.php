<?php

namespace App\Entity;

use App\Repository\PanierRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PanierRepository::class)]
class Panier
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id_panier = null;

  

    #[ORM\Column(type: "float")]
    private ?float $totale = null;

    #[ORM\Column(type: "float")]
    private ?float $remise = null;

    #[ORM\OneToMany(targetEntity: LigneCommande::class, mappedBy: 'panier')]
    private Collection $lignesCommande;

    #[ORM\ManyToOne(targetEntity: Utilisateur::class, inversedBy: 'panierUtilisateur')]
    #[ORM\JoinColumn(name: "id_client", referencedColumnName: "id_utilisateur")]
    private ?Utilisateur $utilisateur;
    
    
    public function getUtilisateur(): ?Utilisateur
    {
        return $this->utilisateur;
    }
    
    public function setUtilisateur(?Utilisateur $utilisateur): self
    {
        $this->utilisateur = $utilisateur;
        return $this;
    }

    public function __construct()
    {
        $this->lignesCommande = new ArrayCollection();
    }

    public function getId_panier(): ?int
    {
        return $this->id_panier;
    }

 

    public function getTotale(): ?float
    {
        return $this->totale;
    }

    public function setTotale(float $totale): self
    {
        $this->totale = $totale;

        return $this;
    }

    public function getRemise(): ?float
    {
        return $this->remise;
    }

    public function setRemise(float $remise): self
    {
        $this->remise = $remise;

        return $this;
    }

    public function addLigneCommande(LigneCommande $ligneCommande): self
    {
        if (!$this->lignesCommande->contains($ligneCommande)) {
            $this->lignesCommande[] = $ligneCommande;
            $ligneCommande->setPanier($this);
        }

        return $this;
    }

    public function removeLigneCommande(LigneCommande $ligneCommande): self
    {
        if ($this->lignesCommande->removeElement($ligneCommande)) {
            // set the owning side to null (unless already changed)
            if ($ligneCommande->getPanier() === $this) {
                $ligneCommande->setPanier(null);
            }
        }

        return $this;
    }

    public function getLignesCommande(): Collection
    {
        return $this->lignesCommande;
    }




  

  

  

   

    


}
