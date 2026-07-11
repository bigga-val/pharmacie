<?php

namespace App\Entity;

use App\Repository\PaieEmployeRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PaieEmployeRepository::class)]
#[ORM\UniqueConstraint(name: 'unique_employe_paie', columns: ['employe_id', 'paie_id'])]
class PaieEmploye
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Employe $Employe = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Paie $Paie = null;

    #[ORM\Column]
    private int $nbJours = 0;

    #[ORM\Column(nullable: true)]
    private ?float $salaireBase = null;

    #[ORM\Column(nullable: true)]
    private ?float $primes = 0;

    #[ORM\Column(nullable: true)]
    private ?float $deductions = 0;

    #[ORM\Column(nullable: true)]
    private ?float $total = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $createdAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmploye(): ?Employe
    {
        return $this->Employe;
    }

    public function setEmploye(?Employe $Employe): static
    {
        $this->Employe = $Employe;

        return $this;
    }

    public function getPaie(): ?Paie
    {
        return $this->Paie;
    }

    public function setPaie(?Paie $Paie): static
    {
        $this->Paie = $Paie;

        return $this;
    }

    public function getNbJours(): int
    {
        return $this->nbJours;
    }

    public function setNbJours(int $nbJours): static
    {
        $this->nbJours = $nbJours;

        return $this;
    }

    public function getSalaireBase(): ?float
    {
        return $this->salaireBase;
    }

    public function setSalaireBase(?float $salaireBase): static
    {
        $this->salaireBase = $salaireBase;

        return $this;
    }

    public function getPrimes(): ?float
    {
        return $this->primes;
    }

    public function setPrimes(?float $primes): static
    {
        $this->primes = $primes;

        return $this;
    }

    public function getDeductions(): ?float
    {
        return $this->deductions;
    }

    public function setDeductions(?float $deductions): static
    {
        $this->deductions = $deductions;

        return $this;
    }

    public function getTotal(): ?float
    {
        return $this->total;
    }

    public function setTotal(?float $total): static
    {
        $this->total = $total;

        return $this;
    }

    public function calculerTotal(): void
    {
        $this->total = ($this->salaireBase ?? 0) + ($this->primes ?? 0) - ($this->deductions ?? 0);
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
}
