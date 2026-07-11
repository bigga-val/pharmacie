<?php

namespace App\MessageHandler;

use App\Entity\AuditLog;
use App\Message\AuditLogMessage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class AuditLogMessageHandler
{
    public function __construct(private readonly EntityManagerInterface $entityManager) {}

    public function __invoke(AuditLogMessage $message): void
    {
        $log = new AuditLog();
        $log->setUserEmail($message->getUserEmail());
        $log->setEntityName($message->getEntityName());
        $log->setEntityId($message->getEntityId());
        $log->setAction($message->getAction());
        $log->setOldData($message->getOldData());
        $log->setNewData($message->getNewData());
        $log->setIpAddress($message->getIpAddress());

        $this->entityManager->persist($log);
        $this->entityManager->flush();
    }
}
