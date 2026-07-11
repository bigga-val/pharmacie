<?php

namespace App\Entity;

use App\Repository\ProduitsRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProduitsRepository::class)]
class Produits
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $designation = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $fabricant = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $preemption = null;

    #[ORM\Column(nullable: true)]
    private ?float $prix = null;

    #[ORM\Column(nullable: true)]
    private ?float $prixAchat = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $code = null;

    #[ORM\Column(length: 25, nullable: true)]
    private ?string $uniteMesure = null;

    #[ORM\Column(nullable: true)]
    private ?float $maximum = null;

    #[ORM\Column(nullable: true)]
    private ?float $minimum = null;

    #[ORM\ManyToOne]
    private ?CategorieProduit $Categorie = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDesignation(): ?string
    {
        return $this->designation;
    }

    public function setDesignation(?string $designation): static
    {
        $this->designation = $designation;

        return $this;
    }

    public function getFabricant(): ?string
    {
        return $this->fabricant;
    }

    public function setFabricant(?string $fabricant): static
    {
        $this->fabricant = $fabricant;

        return $this;
    }

    public function getPreemption(): ?\DateTimeInterface
    {
        return $this->preemption;
    }

    public function setPreemption(?\DateTimeInterface $preemption): static
    {
        $this->preemption = $preemption;

        return $this;
    }

    public function __toString(): string
    {
        return $this->getDesignation();
    }

    public function getPrix(): ?float
    {
        return $this->prix;
    }

    public function setPrix(?float $prix): static
    {
        $this->prix = $prix;

        return $this;
    }

    public function getPrixAchat(): ?float
    {
        return $this->prixAchat;
    }

    public function setPrixAchat(?float $prixAchat): static
    {
        $this->prixAchat = $prixAchat;

        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(?string $code): static
    {
        $this->code = $code;

        return $this;
    }

    public function getUniteMesure(): ?string
    {
        return $this->uniteMesure;
    }

    public function setUniteMesure(?string $uniteMesure): static
    {
        $this->uniteMesure = $uniteMesure;

        return $this;
    }

    public function getMaximum(): ?float
    {
        return $this->maximum;
    }

    public function setMaximum(?float $maximum): static
    {
        $this->maximum = $maximum;

        return $this;
    }

    public function getMinimum(): ?float
    {
        return $this->minimum;
    }

    public function setMinimum(?float $minimum): static
    {
        $this->minimum = $minimum;

        return $this;
    }

    public function getCategorie(): ?CategorieProduit
    {
        return $this->Categorie;
    }

    public function setCategorie(?CategorieProduit $Categorie): static
    {
        $this->Categorie = $Categorie;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): static
    {
        $this->image = $image;

        return $this;
    }
}
