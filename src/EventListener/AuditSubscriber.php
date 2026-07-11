<?php

namespace App\EventListener;

use App\Entity\AuditLog;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Event\PreRemoveEventArgs;
use Doctrine\ORM\Events;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\RequestStack;

#[AsDoctrineListener(event: Events::postPersist)]
#[AsDoctrineListener(event: Events::postUpdate)]
#[AsDoctrineListener(event: Events::preRemove)]
#[AsDoctrineListener(event: Events::postFlush)]
class AuditSubscriber
{
    private const AUDITABLE = [
        'App\Entity\Vente',
        'App\Entity\Paie',
        'App\Entity\PaieEmploye',
        'App\Entity\Employe',
        'App\Entity\Approvisionnement',
        'App\Entity\Credit',
        'App\Entity\Debit',
        'App\Entity\Produits',
        'App\Entity\Taux',
        'App\Entity\Table',
        'App\Entity\ProduitVendu',
        'App\Entity\User',
    ];

    /** @var AuditLog[] */
    private array $pending = [];
    private bool $flushing = false;

    public function __construct(
        private readonly Security      $security,
        private readonly RequestStack  $requestStack,
    ) {}

    public function postPersist(PostPersistEventArgs $args): void
    {
        $entity = $args->getObject();
        if (!$this->isAuditable($entity)) return;

        $this->pending[] = $this->buildLog('CREATE', $entity, null, $this->extractScalars($entity));
    }

    public function postUpdate(PostUpdateEventArgs $args): void
    {
        $entity = $args->getObject();
        if (!$this->isAuditable($entity)) return;

        $changeSet = $args->getObjectManager()->getUnitOfWork()->getEntityChangeSet($entity);
        $old = [];
        $new = [];
        foreach ($changeSet as $field => [$before, $after]) {
            if ($before instanceof \DateTimeInterface) $before = $before->format('Y-m-d H:i:s');
            if ($after  instanceof \DateTimeInterface) $after  = $after->format('Y-m-d H:i:s');
            if (is_object($before) || is_object($after)) continue;
            $old[$field] = $before;
            $new[$field] = $after;
        }

        $this->pending[] = $this->buildLog('UPDATE', $entity, $old ?: null, $new ?: null);
    }

    public function preRemove(PreRemoveEventArgs $args): void
    {
        $entity = $args->getObject();
        if (!$this->isAuditable($entity)) return;

        $this->pending[] = $this->buildLog('DELETE', $entity, $this->extractScalars($entity), null);
    }

    public function postFlush(PostFlushEventArgs $args): void
    {
        if ($this->flushing || empty($this->pending)) return;

        $this->flushing = true;
        $em = $args->getObjectManager();

        foreach ($this->pending as $log) {
            $em->persist($log);
        }
        $this->pending = [];
        $em->flush();

        $this->flushing = false;
    }

    private function buildLog(string $action, object $entity, ?array $old, ?array $new): AuditLog
    {
        $user    = $this->security->getUser();
        $request = $this->requestStack->getCurrentRequest();

        $log = new AuditLog();
        $log->setUserEmail($user?->getUserIdentifier());
        $log->setEntityName((new \ReflectionClass($entity))->getShortName());
        $log->setEntityId(method_exists($entity, 'getId') ? $entity->getId() : null);
        $log->setAction($action);
        $log->setOldData($old);
        $log->setNewData($new);
        $log->setIpAddress($request?->getClientIp());

        return $log;
    }

    private function isAuditable(object $entity): bool
    {
        return in_array(get_class($entity), self::AUDITABLE, true);
    }

    private function extractScalars(object $entity): array
    {
        $data = [];
        $ref  = new \ReflectionClass($entity);
        foreach ($ref->getProperties() as $prop) {
            $prop->setAccessible(true);
            $value = $prop->getValue($entity);
            if ($value instanceof \DateTimeInterface) {
                $data[$prop->getName()] = $value->format('Y-m-d H:i:s');
            } elseif (is_scalar($value) || $value === null) {
                $data[$prop->getName()] = $value;
            }
        }
        return $data;
    }
}
