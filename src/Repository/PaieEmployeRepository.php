<?php

namespace App\Repository;

use App\Entity\PaieEmploye;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PaieEmploye>
 *
 * @method PaieEmploye|null find($id, $lockMode = null, $lockVersion = null)
 * @method PaieEmploye|null findOneBy(array $criteria, array $orderBy = null)
 * @method PaieEmploye[]    findAll()
 * @method PaieEmploye[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PaieEmployeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PaieEmploye::class);
    }

//    /**
//     * @return PaieEmploye[] Returns an array of PaieEmploye objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('p.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?PaieEmploye
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }

    public function totalParPeriode(?\DateTimeInterface $debut, ?\DateTimeInterface $fin): float
    {
        $qb = $this->createQueryBuilder('pe')->select('SUM(pe.total)');

        if ($debut) $qb->andWhere('pe.createdAt >= :debut')->setParameter('debut', $debut->format('Y-m-d') . ' 00:00:00');
        if ($fin)   $qb->andWhere('pe.createdAt <= :fin')->setParameter('fin',   $fin->format('Y-m-d')   . ' 23:59:59');

        return (float) ($qb->getQuery()->getSingleScalarResult() ?? 0);
    }

    public function masseSalarialeMoisCourant(): float
    {
        $mois   = (int) date('n');
        $annee  = (int) date('Y');
        $result = $this->createQueryBuilder('pe')
            ->select('SUM(pe.total)')
            ->join('pe.Paie', 'p')
            ->where('p.MonthPay = :mois AND p.YearPay = :annee')
            ->setParameter('mois', $mois)
            ->setParameter('annee', $annee)
            ->getQuery()->getSingleScalarResult();
        return (float) ($result ?? 0);
    }
}
