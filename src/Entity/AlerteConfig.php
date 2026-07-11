<?php

namespace App\Entity;

use App\Repository\AlerteConfigRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AlerteConfigRepository::class)]
class AlerteConfig
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private bool $actifStockBas = true;

    #[ORM\Column]
    private bool $actifPeremption = true;

    #[ORM\Column]
    private int $joursAvantPeremption = 30;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function isActifStockBas(): bool
    {
        return $this->actifStockBas;
    }

    public function setActifStockBas(bool $actifStockBas): static
    {
        $this->actifStockBas = $actifStockBas;
        return $this;
    }

    public function isActifPeremption(): bool
    {
        return $this->actifPeremption;
    }

    public function setActifPeremption(bool $actifPeremption): static
    {
        $this->actifPeremption = $actifPeremption;
        return $this;
    }

    public function getJoursAvantPeremption(): int
    {
        return $this->joursAvantPeremption;
    }

    public function setJoursAvantPeremption(int $joursAvantPeremption): static
    {
        $this->joursAvantPeremption = $joursAvantPeremption;
        return $this;
    }
}
