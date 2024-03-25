<?php

namespace App\Entity;

use App\Repository\AlerteRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AlerteRepository::class)]
class Alerte
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id_alerte = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $description_alerte = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $date_alerte = null;

    #[ORM\Column]
    private ?bool $Type = null;

    public function getId_alerte(): ?int
    {
        return $this->id_alerte;
    }

    public function getDescriptionAlerte(): ?string
    {
        return $this->description_alerte;
    }

    public function setDescriptionAlerte(?string $description_alerte): static
    {
        $this->description_alerte = $description_alerte;

        return $this;
    }

    public function getDateAlerte(): ?\DateTimeInterface
    {
        return $this->date_alerte;
    }

    public function setDateAlerte(\DateTimeInterface $date_alerte): static
    {
        $this->date_alerte = $date_alerte;

        return $this;
    }

    public function isType(): ?bool
    {
        return $this->Type;
    }

    public function setType(bool $Type): static
    {
        $this->Type = $Type;

        return $this;
    }
}
