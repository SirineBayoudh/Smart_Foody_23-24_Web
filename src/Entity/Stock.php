<?php

namespace App\Entity;

use App\Repository\StockRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: StockRepository::class)]
class Stock
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id_s = null;


    #[ORM\Column]
    #[Assert\NotNull(message: "Le champ est obligatoire")]
    #[Assert\Positive(message: "La quantité doit être positive")]
    private ?int $quantite;

    #[ORM\Column(nullable: true)]

    private ?int $nbVendu = null;

    #[ORM\Column(nullable: true)]
    private ?float $cout = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le champ ne peut pas être vide")]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le champ ne peut pas être vide")]
    private ?string $marque = null;
    #[ORM\Column(length: 255, nullable: true)]

    private ?string $image = null;
    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    #[Assert\NotBlank(message: "Le champ ne peut pas être vide")]
    private ?\DateTimeInterface $date_arrivage = null;

    #[ORM\ManyToOne(inversedBy: 'ref_produit')]
    #[ORM\JoinColumn(name: "ref_produit", referencedColumnName: "ref")]
    #[Assert\NotBlank(message: "Le champ ne peut pas être vide")]
    private ?Produit $ref_produit = null;

    #[ORM\OneToMany(mappedBy: 'id_stock', targetEntity: Alerte::class)]
    private Collection $id_stock;

    public function __construct()
    {
        $this->id_stock = new ArrayCollection();
    }

    public function getId_s(): ?int
    {
        return $this->id_s;
    }

    public function getQuantite(): ?int
    {
        return $this->quantite;
    }

    public function setQuantite(int $quantite): static
    {
        $this->quantite = $quantite;

        return $this;
    }

    public function getNbVendu(): ?int
    {
        return $this->nbVendu;
    }

    public function setNbVendu(?int $nbVendu): static
    {
        $this->nbVendu = $nbVendu;

        return $this;
    }

    public function getCout(): ?float
    {
        return $this->cout;
    }

    public function setCout(?float $cout): static
    {
        $this->cout = $cout;

        return $this;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
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

    public function getDateArrivage(): ?\DateTimeInterface
    {
        return $this->date_arrivage;
    }

    public function setDateArrivage(?\DateTimeInterface $date_arrivage): static
    {
        $this->date_arrivage = $date_arrivage;

        return $this;
    }

    // public function getProduit(): ?Produit
    // {
    //     return $this->produit;
    // }

    // public function setProduit(?Produit $produit): static
    // {
    //     $this->produit = $produit;

    //     return $this;
    // }
    public function getRefProduit(): ?Produit
    {
        return $this->ref_produit;
    }

    public function setRefProduit(?Produit $ref_produit): static
    {
        $this->ref_produit = $ref_produit;

        return $this;
    }

    /**
     * Get the value of image
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * Set the value of image
     *
     * @return  self
     */
    public function setImage($image)
    {
        $this->image = $image;

        return $this;
    }

    /**
     * @return Collection<int, alerte>
     */
    public function getIdStock(): Collection
    {
        return $this->id_stock;
    }

    public function addIdStock(alerte $idStock): static
    {
        if (!$this->id_stock->contains($idStock)) {
            $this->id_stock->add($idStock);
            $idStock->setId_Stock($this);
        }

        return $this;
    }

    public function removeIdStock(alerte $idStock): static
    {
        if ($this->id_stock->removeElement($idStock)) {
            // set the owning side to null (unless already changed)
            if ($idStock->getId_Stock() === $this) {
                $idStock->setId_Stock(null);
            }
        }

        return $this;
    }
}
