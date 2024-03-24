<?php

namespace App\Entity;

use App\Repository\AvisRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AvisRepository::class)]
class Avis
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id_avis = null;

    #[ORM\Column]
    private ?int $nb_etoiles = null;

    #[ORM\Column(length: 255)]
    private ?string $commentaire = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $date_avis = null;

    #[ORM\Column]
    private ?int $signaler = null;

    #[ORM\ManyToOne(inversedBy: 'avis')]
    #[ORM\JoinColumn(name: "id_client", referencedColumnName: "id_utilisateur")]
    private ?Utilisateur $id_client = null;

    #[ORM\ManyToOne(inversedBy: 'avis')]
    #[ORM\JoinColumn(name: "ref_produit", referencedColumnName: "ref")]
    private ?Produit $ref_produit = null;

    public function getId(): ?int
    {
        return $this->id_avis;
    }

    public function getNbEtoiles(): ?int
    {
        return $this->nb_etoiles;
    }

    public function setNbEtoiles(int $nb_etoiles): static
    {
        $this->nb_etoiles = $nb_etoiles;

        return $this;
    }

    public function getCommentaire(): ?string
    {
        return $this->commentaire;
    }

    public function setCommentaire(string $commentaire): static
    {
        $this->commentaire = $commentaire;

        return $this;
    }

    public function getDateAvis(): ?\DateTimeInterface
    {
        return $this->date_avis;
    }

    public function setDateAvis(\DateTimeInterface $date_avis): static
    {
        $this->date_avis = $date_avis;

        return $this;
    }

    public function getSignaler(): ?int
    {
        return $this->signaler;
    }

    public function setSignaler(int $signaler): static
    {
        $this->signaler = $signaler;

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
