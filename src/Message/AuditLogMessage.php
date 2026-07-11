<?php

namespace App\Message;

class AuditLogMessage
{
    public function __construct(
        private readonly ?string $userEmail,
        private readonly string  $entityName,
        private readonly ?int    $entityId,
        private readonly string  $action,
        private readonly ?array  $oldData,
        private readonly ?array  $newData,
        private readonly ?string $ipAddress,
    ) {}

    public function getUserEmail(): ?string  { return $this->userEmail; }
    public function getEntityName(): string  { return $this->entityName; }
    public function getEntityId(): ?int      { return $this->entityId; }
    public function getAction(): string      { return $this->action; }
    public function getOldData(): ?array     { return $this->oldData; }
    public function getNewData(): ?array     { return $this->newData; }
    public function getIpAddress(): ?string  { return $this->ipAddress; }
}
