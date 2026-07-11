<?php

namespace App\Repository;

use App\Entity\Credit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Credit>
 *
 * @method Credit|null find($id, $lockMode = null, $lockVersion = null)
 * @method Credit|null findOneBy(array $criteria, array $orderBy = null)
 * @method Credit[]    findAll()
 * @method Credit[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CreditRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Credit::class);
    }

//    /**
//     * @return Credit[] Returns an array of Credit objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('c.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Credit
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }

    public function totalMoisCourant(): float
    {
        $debut = new \DateTime('first day of this month');
        $fin   = new \DateTime('last day of this month');
        $result = $this->createQueryBuilder('c')
            ->select('SUM(c.montant)')
            ->where('c.dateCredit BETWEEN :debut AND :fin')
            ->setParameter('debut', $debut->format('Y-m-d'))
            ->setParameter('fin', $fin->format('Y-m-d'))
            ->getQuery()->getSingleScalarResult();
        return (float) ($result ?? 0);
    }

    public function totauxParPeriode(?\DateTimeInterface $debut, ?\DateTimeInterface $fin): array
    {
        $qb = $this->createQueryBuilder('c')
            ->select('c.devise, SUM(c.montant) as total')
            ->groupBy('c.devise');

        if ($debut) $qb->andWhere('c.dateCredit >= :debut')->setParameter('debut', $debut->format('Y-m-d'));
        if ($fin)   $qb->andWhere('c.dateCredit <= :fin')->setParameter('fin', $fin->format('Y-m-d'));

        $rows = $qb->getQuery()->getResult();

        $totaux = ['FC' => 0.0, 'USD' => 0.0];
        foreach ($rows as $row) {
            $devise = strtoupper($row['devise'] ?? 'FC');
            $totaux[$devise] = (float) ($row['total'] ?? 0);
        }
        return $totaux;
    }

    public function sortiepardate($dateDebut, $dateFin): array{
        $em = $this->getEntityManager();
        $query = $em->createQuery(
            'SELECT SUM(c.montant) montant         
                    FROM App\Entity\Credit c
                     where c.dateCredit between :dateDebut and :dateFin
                    --GROUP BY v.venteDate 
            '
        );
        //$query->setParameter('mydate', $mydate);
        $query->setParameter('dateDebut', $dateDebut);
        $query->setParameter('dateFin', $dateFin);
        return $query->getResult();
    }
}
