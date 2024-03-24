<?php

namespace App\Entity;

use App\Repository\ReclamationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReclamationRepository::class)]
class Reclamation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id_reclamation = null;

    #[ORM\Column(length: 255)]
    private ?string $description = null;

    #[ORM\Column(length: 255)]
    private ?string $titre = null;

    #[ORM\Column(length: 255)]
    private ?string $statut = null;

    #[ORM\Column(length: 255)]
    private ?string $type = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $date_reclamation = null;

    #[ORM\Column]
    private ?int $archive = null;

    #[ORM\ManyToOne(inversedBy: 'reclamations')]
    #[ORM\JoinColumn(name: "id_client", referencedColumnName: "id_utilisateur")]
    private ?Utilisateur $id_client = null;

    public function getId(): ?int
    {
        return $this->id_reclamation;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): static
    {
        $this->titre = $titre;

        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): static
    {
        $this->statut = $statut;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getDateReclamation(): ?\DateTimeInterface
    {
        return $this->date_reclamation;
    }

    public function setDateReclamation(\DateTimeInterface $date_reclamation): static
    {
        $this->date_reclamation = $date_reclamation;

        return $this;
    }

    public function getArchive(): ?int
    {
        return $this->archive;
    }

    public function setArchive(int $archive): static
    {
        $this->archive = $archive;

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
