<?php

namespace App\Entity;

use App\Repository\CategorieProduitRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CategorieProduitRepository::class)]
class CategorieProduit
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $designation = null;

    #[ORM\Column(nullable: true)]
    private ?float $Pourcentage = null;

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

    public function getPourcentage(): ?float
    {
        return $this->Pourcentage;
    }

    public function setPourcentage(?float $Pourcentage): static
    {
        $this->Pourcentage = $Pourcentage;

        return $this;
    }
}
