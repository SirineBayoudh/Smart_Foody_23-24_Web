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

    #[ORM\Column]
    private ?float $totale = null;

    #[ORM\Column]
    private ?float $remise = null;

    #[ORM\OneToMany(mappedBy: 'id_panier', targetEntity: LigneCommande::class)]
    private Collection $ligneCommandes;

    #[ORM\ManyToOne(inversedBy: 'paniers')]
    #[ORM\JoinColumn(name: "id_client", referencedColumnName: "id_utilisateur")]
    private ?Utilisateur $id_client = null;

    public function __construct()
    {
        $this->ligneCommandes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id_panier;
    }

    public function getTotale(): ?float
    {
        return $this->totale;
    }

    public function setTotale(float $totale): static
    {
        $this->totale = $totale;

        return $this;
    }

    public function getRemise(): ?float
    {
        return $this->remise;
    }

    public function setRemise(float $remise): static
    {
        $this->remise = $remise;

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
            $ligneCommande->setIdPanier($this);
        }

        return $this;
    }

    public function removeLigneCommande(LigneCommande $ligneCommande): static
    {
        if ($this->ligneCommandes->removeElement($ligneCommande)) {
            // set the owning side to null (unless already changed)
            if ($ligneCommande->getIdPanier() === $this) {
                $ligneCommande->setIdPanier(null);
            }
        }

        return $this;
    }

    public function getIdClient(): ?Utilisateur
    {
        return $this->id_client;
    }

    public function setIdClient(?Utilisateur $id_client): static
    {
        $this->id_client = $id_client;

        return $this;
    }
}
