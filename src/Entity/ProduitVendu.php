<?php

namespace App\Entity;

use App\Repository\ProduitVenduRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProduitVenduRepository::class)]
class ProduitVendu
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    private ?Vente $vente = null;

    #[ORM\Column(nullable: true)]
    private ?float $qty = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $createdby = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\ManyToOne]
    private ?Produits $produit = null;

    #[ORM\Column(nullable: true)]
    private ?float $prixUnitaire = null;

    #[ORM\Column(nullable: true)]
    private ?float $taux = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getVente(): ?Vente
    {
        return $this->vente;
    }

    public function setVente(?Vente $vente): static
    {
        $this->vente = $vente;

        return $this;
    }

    public function getQty(): ?float
    {
        return $this->qty;
    }

    public function setQty(?float $qty): static
    {
        $this->qty = $qty;

        return $this;
    }

    public function getCreatedby(): ?string
    {
        return $this->createdby;
    }

    public function setCreatedby(?string $createdby): static
    {
        $this->createdby = $createdby;

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

    public function getProduit(): ?Produits
    {
        return $this->produit;
    }

    public function setProduit(?Produits $produit): static
    {
        $this->produit = $produit;

        return $this;
    }

    public function getPrixUnitaire(): ?float
    {
        return $this->prixUnitaire;
    }

    public function setPrixUnitaire(?float $prixUnitaire): static
    {
        $this->prixUnitaire = $prixUnitaire;

        return $this;
    }

    public function getTaux(): ?float
    {
        return $this->taux;
    }

    public function setTaux(?float $taux): static
    {
        $this->taux = $taux;

        return $this;
    }
}
