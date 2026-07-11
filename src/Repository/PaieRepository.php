<?php

namespace App\Repository;

use App\Entity\Paie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Paie>
 *
 * @method Paie|null find($id, $lockMode = null, $lockVersion = null)
 * @method Paie|null findOneBy(array $criteria, array $orderBy = null)
 * @method Paie[]    findAll()
 * @method Paie[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PaieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Paie::class);
    }

//    /**
//     * @return Paie[] Returns an array of Paie objects
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

//    public function findOneBySomeField($value): ?Paie
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
