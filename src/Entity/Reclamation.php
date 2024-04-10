<?php

namespace App\Entity;

use Symfony\Component\Validator\Constraints as Assert; //Pour le controlle de saisie

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

    
    #[ORM\Column(length: 5000)]
    #[Assert\NotBlank(message:"champs obligatoire")]
    private ?string $description = null;

     
    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message:"champs obligatoire")]
    private ?string $titre = null;

    #[ORM\Column(length: 255)]
    private $statut = 'Attente'; // Valeur par défaut pour le statu

    #[ORM\Column(length: 255)]
    private ?string $type = null;

    /**
     * @ORM\Column(type="datetime")
     */
    private $date_reclamation;

    #[ORM\Column]
    private ?int $archive = 0;

    #[ORM\ManyToOne(inversedBy: 'reclamations')]
    #[ORM\JoinColumn(name: "id_client", referencedColumnName: "id_utilisateur")]
    private ?Utilisateur $id_client = null;


    private $utilisateur;

    /**
     * @ORM\Column(length=255)
     */
    private ?string $nom = null;

    /**
     * @ORM\Column(length=255)
     */
    private ?string $prenom = null;

    /**
     * @ORM\Column(length=255)
     */
    private ?string $email = null;

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(?string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(?string $prenom): self
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

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
        $this->date_reclamation = new \DateTime(); // Valeur par défaut pour la date de réclamation
    }





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
