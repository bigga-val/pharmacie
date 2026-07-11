<?php

namespace App\Entity;

use App\Repository\AuditLogRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AuditLogRepository::class)]
#[ORM\Table(name: 'audit_log')]
class AuditLog
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $userEmail = null;

    #[ORM\Column(length: 100)]
    private string $entityName;

    #[ORM\Column(nullable: true)]
    private ?int $entityId = null;

    #[ORM\Column(length: 20)]
    private string $action; // CREATE, UPDATE, DELETE

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $oldData = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $newData = null;

    #[ORM\Column(length: 45, nullable: true)]
    private ?string $ipAddress = null;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }

    public function getUserEmail(): ?string { return $this->userEmail; }
    public function setUserEmail(?string $userEmail): static { $this->userEmail = $userEmail; return $this; }

    public function getEntityName(): string { return $this->entityName; }
    public function setEntityName(string $entityName): static { $this->entityName = $entityName; return $this; }

    public function getEntityId(): ?int { return $this->entityId; }
    public function setEntityId(?int $entityId): static { $this->entityId = $entityId; return $this; }

    public function getAction(): string { return $this->action; }
    public function setAction(string $action): static { $this->action = $action; return $this; }

    public function getOldData(): ?array { return $this->oldData; }
    public function setOldData(?array $oldData): static { $this->oldData = $oldData; return $this; }

    public function getNewData(): ?array { return $this->newData; }
    public function setNewData(?array $newData): static { $this->newData = $newData; return $this; }

    public function getIpAddress(): ?string { return $this->ipAddress; }
    public function setIpAddress(?string $ipAddress): static { $this->ipAddress = $ipAddress; return $this; }

    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
    public function setCreatedAt(\DateTimeImmutable $createdAt): static { $this->createdAt = $createdAt; return $this; }
}
