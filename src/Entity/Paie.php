<?php

namespace App\Entity;

use App\Repository\PaieRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PaieRepository::class)]
class Paie
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $label = null;

    #[ORM\Column(nullable: true)]
    private ?int $MonthPay = null;

    #[ORM\Column(type: Types::SMALLINT, nullable: true)]
    private ?int $YearPay = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(?string $label): static
    {
        $this->label = $label;

        return $this;
    }

    public function getMonthPay(): ?int
    {
        return $this->MonthPay;
    }

    public function setMonthPay(?int $MonthPay): static
    {
        $this->MonthPay = $MonthPay;

        return $this;
    }

    public function getYearPay(): ?int
    {
        return $this->YearPay;
    }

    public function setYearPay(?int $YearPay): static
    {
        $this->YearPay = $YearPay;

        return $this;
    }
}
