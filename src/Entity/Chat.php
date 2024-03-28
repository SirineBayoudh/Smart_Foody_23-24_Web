<?php

namespace App\Entity;

use App\Repository\ChatRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as assert;

#[ORM\Entity(repositoryClass: ChatRepository::class)]
class Chat
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id_chat = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message:"champs obligatoire")]
    private ?string $question = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message:"champs obligatoire")]
    private ?string $reponse = null;

    public function getIdChat(): ?int
    {
        return $this->id_chat;
    }

    public function getQuestion(): ?string
    {
        return $this->question;
    }

    public function setQuestion(string $question): static
    {
        $this->question = $question;

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
}
