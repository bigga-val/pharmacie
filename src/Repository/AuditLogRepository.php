<?php

namespace App\Repository;

use App\Entity\AuditLog;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class AuditLogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AuditLog::class);
    }

    public function findFiltered(?string $userEmail, ?string $entityName, ?string $action, ?\DateTimeImmutable $from, ?\DateTimeImmutable $to): array
    {
        $qb = $this->createQueryBuilder('a')->orderBy('a.createdAt', 'DESC');

        if ($userEmail) {
            $qb->andWhere('a.userEmail LIKE :user')->setParameter('user', '%'.$userEmail.'%');
        }
        if ($entityName) {
            $qb->andWhere('a.entityName = :entity')->setParameter('entity', $entityName);
        }
        if ($action) {
            $qb->andWhere('a.action = :action')->setParameter('action', $action);
        }
        if ($from) {
            $qb->andWhere('a.createdAt >= :from')->setParameter('from', $from);
        }
        if ($to) {
            $qb->andWhere('a.createdAt <= :to')->setParameter('to', $to);
        }

        return $qb->setMaxResults(500)->getQuery()->getResult();
    }

    public function findDistinctEntityNames(): array
    {
        return $this->createQueryBuilder('a')
            ->select('DISTINCT a.entityName')
            ->orderBy('a.entityName', 'ASC')
            ->getQuery()
            ->getSingleColumnResult();
    }
}
