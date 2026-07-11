<?php

namespace App\Entity;

use App\Repository\EmployeRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EmployeRepository::class)]
class Employe
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nomcomplet = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $matricule = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateembauche = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $categorie = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $Titre = null;

    #[ORM\Column(nullable: true)]
    private ?float $salaireJournalier = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNomcomplet(): ?string
    {
        return $this->nomcomplet;
    }

    public function setNomcomplet(?string $nomcomplet): static
    {
        $this->nomcomplet = $nomcomplet;

        return $this;
    }

    public function getMatricule(): ?string
    {
        return $this->matricule;
    }

    public function setMatricule(?string $matricule): static
    {
        $this->matricule = $matricule;

        return $this;
    }

    public function getDateembauche(): ?\DateTimeInterface
    {
        return $this->dateembauche;
    }

    public function setDateembauche(?\DateTimeInterface $dateembauche): static
    {
        $this->dateembauche = $dateembauche;

        return $this;
    }

    public function getCategorie(): ?string
    {
        return $this->categorie;
    }

    public function setCategorie(?string $categorie): static
    {
        $this->categorie = $categorie;

        return $this;
    }

    public function getTitre(): ?string
    {
        return $this->Titre;
    }

    public function setTitre(?string $Titre): static
    {
        $this->Titre = $Titre;

        return $this;
    }

    public function getSalaireJournalier(): ?float
    {
        return $this->salaireJournalier;
    }

    public function setSalaireJournalier(?float $salaireJournalier): static
    {
        $this->salaireJournalier = $salaireJournalier;

        return $this;
    }
}
