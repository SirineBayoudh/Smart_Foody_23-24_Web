<?php

namespace App\Entity;

use App\Repository\CommandeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CommandeRepository::class)]
class Commande
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;



    public function getId(): ?int
    {
        return $this->id;
    }

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $dateCommande = null;



    #[ORM\Column(name: "totalecommande", type: "float")]
    private ?float $totaleCommande = null;

    #[ORM\Column(type: "float")]
    private ?float $remise = null;

    #[ORM\Column(length: 255)]
    private ?string $etat = null;

    #[ORM\Column(type: "float")]
    private ?float $longitude = null;

    #[ORM\Column(type: "float")]
    private ?float $latitude = null;

    #[ORM\Column(length: 255)]
    private ?string $address = null;

    #[ORM\OneToMany(targetEntity: LigneCommande::class, mappedBy: 'commande')]
    private Collection $lignesCommande;

  #[ORM\ManyToOne(targetEntity: Utilisateur::class, inversedBy: 'commandes')]
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



    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): self // Correction du type de retour et du paramètre
    {
        $this->address = $address;
        return $this;
    }

  

  public function addLigneCommande(LigneCommande $ligneCommande): self
  {
      if (!$this->lignesCommande->contains($ligneCommande)) {
          $this->lignesCommande[] = $ligneCommande;
          $ligneCommande->setCommande($this);
      }

      return $this;
  }

  public function removeLigneCommande(LigneCommande $ligneCommande): self
  {
      if ($this->lignesCommande->removeElement($ligneCommande)) {
          if ($ligneCommande->getCommande() === $this) {
              $ligneCommande->setCommande(null);
          }
      }

      return $this;
  }
  public function getLignesCommande(): Collection
    {
        return $this->lignesCommande;
    }

  public function getIdCommande(): ?int
  {
      return $this->id;
  }


    public function getDateCommande(): ?\DateTimeInterface
    {
        return $this->dateCommande;
    }

    public function setDateCommande(\DateTimeInterface $dateCommande): static
    {
        $this->dateCommande = $dateCommande;

        return $this;
    }


    public function getTotaleCommande(): ?float
    {
        return $this->totaleCommande;
    }

    public function setTotaleCommande(float $totaleCommande): static
    {
        $this->totaleCommande = $totaleCommande;

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

    public function getEtat(): ?string
    {
        return $this->etat;
    }

 
    public function setEtat(string $etat): static
{
    $this->etat = $etat;
    return $this;
}

    public function getLongitude(): ?float
    {
        return $this->longitude;
    }



    public function setLatitude(float $latitude): self
{
    $this->latitude = $latitude;
    return $this;
}

public function setLongitude(float $longitude): self
{
    $this->longitude = $longitude;
    return $this;
}

    public function getLatitude(): ?float
    {
        return $this->latitude;
    }

   }

   




  

 





