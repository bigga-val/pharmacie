<?php

namespace App\Repository;

use App\Entity\Debit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Debit>
 *
 * @method Debit|null find($id, $lockMode = null, $lockVersion = null)
 * @method Debit|null findOneBy(array $criteria, array $orderBy = null)
 * @method Debit[]    findAll()
 * @method Debit[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DebitRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Debit::class);
    }

//    /**
//     * @return Debit[] Returns an array of Debit objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('d')
//            ->andWhere('d.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('d.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

    public function totalMoisCourant(): float
    {
        $debut = new \DateTime('first day of this month');
        $fin   = new \DateTime('last day of this month');
        $result = $this->createQueryBuilder('d')
            ->select('SUM(d.montant)')
            ->where('d.DateDebit BETWEEN :debut AND :fin')
            ->setParameter('debut', $debut->format('Y-m-d'))
            ->setParameter('fin', $fin->format('Y-m-d'))
            ->getQuery()->getSingleScalarResult();
        return (float) ($result ?? 0);
    }

    public function totauxParPeriode(?\DateTimeInterface $debut, ?\DateTimeInterface $fin): array
    {
        $qb = $this->createQueryBuilder('d')
            ->select('d.devise, SUM(d.montant) as total')
            ->groupBy('d.devise');

        if ($debut) $qb->andWhere('d.DateDebit >= :debut')->setParameter('debut', $debut->format('Y-m-d'));
        if ($fin)   $qb->andWhere('d.DateDebit <= :fin')->setParameter('fin', $fin->format('Y-m-d'));

        $rows = $qb->getQuery()->getResult();

        $totaux = ['FC' => 0.0, 'USD' => 0.0];
        foreach ($rows as $row) {
            $devise = strtoupper($row['devise'] ?? 'FC');
            $totaux[$devise] = (float) ($row['total'] ?? 0);
        }
        return $totaux;
    }

    public function findDebitByDatesIntervalle($date1, $date2): array
    {

        return $this->createQueryBuilder('d')
            ->andWhere('d.DateDebit BETWEEN :start AND :end')
            ->setParameter('start', $date1)
            ->setParameter('end', $date2)
            ->OrderBy('d.createdAt', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }
}
