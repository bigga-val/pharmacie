<?php

namespace App\Entity;

use App\Repository\TauxRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TauxRepository::class)]
class Taux
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(nullable: true)]
    private ?float $cout = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\ManyToOne]
    private ?User $user = null;

    #[ORM\Column(nullable: true)]
    private ?bool $isActive = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function isIsActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(?bool $isActive): static
    {
        $this->isActive = $isActive;

        return $this;
    }
}
