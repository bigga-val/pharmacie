<?php

namespace App\Repository;

use App\Entity\Produits;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Produits>
 *
 * @method Produits|null find($id, $lockMode = null, $lockVersion = null)
 * @method Produits|null findOneBy(array $criteria, array $orderBy = null)
 * @method Produits[]    findAll()
 * @method Produits[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProduitsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Produits::class);
    }

    /**
     * Retourne les produits dont la date de péremption est dépassée
     * ou arrive dans les $jours prochains jours.
     * Ne retourne que les produits ayant une date de péremption définie.
     */
    public function search(string $q): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.designation LIKE :q OR p.fabricant LIKE :q OR p.code LIKE :q')
            ->setParameter('q', '%' . $q . '%')
            ->orderBy('p.designation', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();
    }

    public function findByExpirationAlert(int $jours = 30): array
    {
        $limitDate = new \DateTime("+{$jours} days");

        return $this->createQueryBuilder('p')
            ->where('p.preemption IS NOT NULL')
            ->andWhere('p.preemption <= :limitDate')
            ->setParameter('limitDate', $limitDate)
            ->orderBy('p.preemption', 'ASC')
            ->getQuery()
            ->getResult();
    }

//    /**
//     * @return Produits[] Returns an array of Produits objects
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

//    public function findOneBySomeField($value): ?Produits
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
