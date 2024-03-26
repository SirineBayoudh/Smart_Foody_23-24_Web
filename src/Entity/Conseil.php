<?php

namespace App\Entity;

use App\Repository\ConseilRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as assert;

#[ORM\Entity(repositoryClass: ConseilRepository::class)]
class Conseil
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id_conseil = null;

    #[ORM\Column(length: 255)]
    private ?string $statut = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message:"champs obligatoire")]
    private ?string $demande = null;

    #[ORM\Column(length: 255)]
    private ?string $reponse = null;

    #[ORM\Column(nullable: true)]
    private ?int $note = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $date_conseil = null;

    #[ORM\ManyToOne(inversedBy: 'conseils')]
    #[ORM\JoinColumn(name: "id_client", referencedColumnName: "id_utilisateur")]
    private ?Utilisateur $id_client = null;

    public function getIdConseil(): ?int
    {
        return $this->id_conseil;
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

    public function getDemande(): ?string
    {
        return $this->demande;
    }

    public function setDemande(string $demande): static
    {
        $this->demande = $demande;

        return $this;
    }

    public function getReponse(): ?string
    {
        return $this->reponse;
    }

    public function setReponse(string $reponse): static
    {
        $this->reponse = $reponse;

        return $this;
    }

    public function getNote(): ?int
    {
        return $this->note;
    }

    public function setNote(?int $note): static
    {
        $this->note = $note;

        return $this;
    }

    public function getDateConseil(): ?\DateTimeInterface
    {
        return $this->date_conseil;
    }

    public function setDateConseil(\DateTimeInterface $date_conseil): static
    {
        $this->date_conseil = $date_conseil;

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
