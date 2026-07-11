<?php

namespace App\Entity;

use App\Repository\VenteRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VenteRepository::class)]
class Vente
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $createdBy = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $numeroVente = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $venteDate = null;

    #[ORM\Column(nullable: true)]
    private ?bool $isApproved = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $statusVente = null;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getCreatedBy(): ?string
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?string $createdBy): static
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    public function getNumeroVente(): ?string
    {
        return $this->numeroVente;
    }

    public function setNumeroVente(?string $numeroVente): static
    {
        $this->numeroVente = $numeroVente;

        return $this;
    }

    public function getVenteDate(): ?\DateTimeInterface
    {
        return $this->venteDate;
    }

    public function setVenteDate(?\DateTimeInterface $venteDate): static
    {
        $this->venteDate = $venteDate;

        return $this;
    }

    public function isIsApproved(): ?bool
    {
        return $this->isApproved;
    }

    public function setIsApproved(?bool $isApproved): static
    {
        $this->isApproved = $isApproved;

        return $this;
    }

    public function getStatusVente(): ?string
    {
        return $this->statusVente;
    }

    public function setStatusVente(?string $statusVente): static
    {
        $this->statusVente = $statusVente;

        return $this;
    }


}
