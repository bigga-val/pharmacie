<?php

namespace App\Repository;

use App\Entity\Vente;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Vente>
 *
 * @method Vente|null find($id, $lockMode = null, $lockVersion = null)
 * @method Vente|null findOneBy(array $criteria, array $orderBy = null)
 * @method Vente[]    findAll()
 * @method Vente[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VenteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Vente::class);
    }

    public function search(string $q): array
    {
        return $this->createQueryBuilder('v')
            ->where('v.numeroVente LIKE :q OR v.createdBy LIKE :q')
            ->setParameter('q', '%' . $q . '%')
            ->orderBy('v.id', 'DESC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();
    }

    public function venteparDate($mydate): array{
        $em = $this->getEntityManager();
        $query = $em->createQuery(
            'SELECT SUM(s.qty * s.prixUnitaire) montant         
                    FROM App\Entity\Produits pr, App\Entity\ProduitVendu s, App\Entity\Vente v
                     where s.produit = pr.id
                          and v.id = s.vente
                          and v.statusVente = :status
                          and v.venteDate = :mydate
                    GROUP BY v.venteDate 
            '
        );
        $query->setParameter('status', 'paid');
        $query->setParameter('mydate', $mydate->format('Y-m-d'));
        return $query->getResult();
    }



    public function venteparIntervale($dateDebut, $dateFin): array{
        $em = $this->getEntityManager();
        $query = $em->createQuery(
            'SELECT SUM(s.qty * s.prixUnitaire) montant         
                    FROM App\Entity\Produits pr, App\Entity\ProduitVendu s, App\Entity\Vente v
                     where s.produit = pr.id
                          and v.id = s.vente
                          and v.statusVente = :status
                          and v.venteDate between :dateDebut and :dateFin
                    GROUP BY v.venteDate 
            '
        );
        //$query->setParameter('mydate', $mydate);
        $query->setParameter('status', 'paid');
        $query->setParameter('dateDebut', $dateDebut);
        $query->setParameter('dateFin', $dateFin);
        return $query->getResult();
    }

    public function venteparIntervale2($dateDebut, $dateFin): array{
        $em = $this->getEntityManager();
        $query = $em->createQuery(
            'SELECT SUM(s.qty * s.prixUnitaire) montant         
                    FROM App\Entity\Produits pr, App\Entity\ProduitVendu s, App\Entity\Vente v
                     where s.produit = pr.id
                          and v.id = s.vente
                          and v.statusVente = :status
                          and v.venteDate between :dateDebut and :dateFin
                    --GROUP BY v.venteDate 
            '
        );
        //$query->setParameter('mydate', $mydate);
        $query->setParameter('dateDebut', $dateDebut);
        $query->setParameter('dateFin', $dateFin);
        $query->setParameter('status', 'paid');

        return $query->getResult();
    }

    //*
    /*
     * Fonction permettant d'afficher les ventes ou factures avec la somme des produits vendus.
     */
    public function venteTotalGrouped(): array{
        $em = $this->getEntityManager();
        $query = $em->createQuery(
            'SELECT SUM(s.qty * s.prixUnitaire) montant, SUM(s.qty * s.prixUnitaire) / s.taux convert, 
            s.taux, v.id, v.createdAt, v.statusVente, v.createdBy, v.numeroVente, s.id ligneID
                    FROM App\Entity\Produits pr, App\Entity\ProduitVendu s, App\Entity\Vente v
                     where s.produit = pr.id
                          and v.id = s.vente
                    GROUP BY v.id
                    ORDER BY v.id DESC
            '
        );
        //$query->setParameter('mydate', $mydate);
        return $query->getResult();
    }

    public function venteAnnuelle(){
        $em = $this->getEntityManager();
        $query = $em->createQuery(
            '
                select substring(pv.createdAt, 6, 2) mois, SUM(pv.qty * pv.prixUnitaire) montant
                from App\Entity\Produits p, App\Entity\ProduitVendu pv, App\Entity\Vente v
                where p.id = pv.produit
                and pv.vente = v.id
                and v.statusVente = :status
                group by mois
            '
        );
        //$query->setParameter('mydate', $mydate);
        $query->setParameter('status', 'paid');
        return $query->getResult();
    }
    public function ApproAnnuel(){
        $em = $this->getEntityManager();
        $query = $em->createQuery(
            '
                select substring(pv.createdAt, 6, 2) mois, SUM(pv.qty * p.prix) montant
                from App\Entity\Produits p, App\Entity\Approvisionnement pv
                where p.id = pv.produit
                group by mois
            '
        );
        //$query->setParameter('mydate', $mydate);
        return $query->getResult();
    }

    public function caHier(): float
    {
        $hier = (new \DateTime('yesterday'))->format('Y-m-d');
        $result = $this->getEntityManager()->createQuery('
            SELECT SUM(pv.qty * pv.prixUnitaire) as montant
            FROM App\Entity\Vente v
            JOIN App\Entity\ProduitVendu pv WITH pv.vente = v
            WHERE v.venteDate = :hier
            AND v.statusVente = :status
        ')
        ->setParameter('hier', $hier)
        ->setParameter('status', 'paid')
        ->getSingleScalarResult();
        return (float) ($result ?? 0);
    }

    public function countHier(): int
    {
        $hier = (new \DateTime('yesterday'))->format('Y-m-d');
        return (int) $this->createQueryBuilder('v')
            ->select('COUNT(v.id)')
            ->where('v.venteDate = :hier')
            ->andWhere('v.statusVente = :status')
            ->setParameter('hier', $hier)
            ->setParameter('status', 'paid')
            ->getQuery()->getSingleScalarResult();
    }

    public function caMoisCourant(): float
    {
        $debut = new \DateTime('first day of this month');
        $fin   = new \DateTime('last day of this month');
        $result = $this->getEntityManager()->createQuery('
            SELECT SUM(pv.qty * pv.prixUnitaire) as montant
            FROM App\Entity\Vente v
            JOIN App\Entity\ProduitVendu pv WITH pv.vente = v
            WHERE v.venteDate BETWEEN :debut AND :fin
            AND v.statusVente = :status
        ')
        ->setParameter('debut', $debut->format('Y-m-d'))
        ->setParameter('fin', $fin->format('Y-m-d'))
        ->setParameter('status', 'paid')
        ->getSingleScalarResult();
        return (float) ($result ?? 0);
    }

    public function countMoisCourant(): int
    {
        $debut = new \DateTime('first day of this month');
        $fin   = new \DateTime('last day of this month');
        return (int) $this->createQueryBuilder('v')
            ->select('COUNT(v.id)')
            ->where('v.venteDate BETWEEN :debut AND :fin')
            ->andWhere('v.statusVente = :status')
            ->setParameter('debut', $debut->format('Y-m-d'))
            ->setParameter('fin', $fin->format('Y-m-d'))
            ->setParameter('status', 'paid')
            ->getQuery()->getSingleScalarResult();
    }

    public function caParMois(int $nbMois = 6): array
    {
        $debut = new \DateTime('first day of -'.($nbMois - 1).' months');
        $fin   = new \DateTime('last day of this month');
        return $this->getEntityManager()->createQuery('
            SELECT SUBSTRING(v.venteDate, 1, 7) as mois, SUM(pv.qty * pv.prixUnitaire) as montant
            FROM App\Entity\Vente v
            JOIN App\Entity\ProduitVendu pv WITH pv.vente = v
            WHERE v.venteDate BETWEEN :debut AND :fin
            AND v.statusVente = :status
            GROUP BY mois
            ORDER BY mois ASC
        ')
        ->setParameter('debut', $debut->format('Y-m-d'))
        ->setParameter('fin', $fin->format('Y-m-d'))
        ->setParameter('status', 'paid')
        ->getResult();
    }

    public function ventesParJourDuMois(): array
    {
        $debut = new \DateTime('first day of this month');
        $fin   = new \DateTime('last day of this month');
        return $this->getEntityManager()->createQuery('
            SELECT SUBSTRING(v.venteDate, 9, 2) as jour, SUM(pv.qty * pv.prixUnitaire) as montant
            FROM App\Entity\Vente v
            JOIN App\Entity\ProduitVendu pv WITH pv.vente = v
            WHERE v.venteDate BETWEEN :debut AND :fin
            AND v.statusVente = :status
            GROUP BY jour
            ORDER BY jour ASC
        ')
        ->setParameter('debut', $debut->format('Y-m-d'))
        ->setParameter('fin', $fin->format('Y-m-d'))
        ->setParameter('status', 'paid')
        ->getResult();
    }

    public function totauxParPeriode(?\DateTimeInterface $debut, ?\DateTimeInterface $fin): array
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
            ->select('SUM(pv.qty * pv.prixUnitaire) as fc')
            ->from('App\Entity\Vente', 'v')
            ->join('App\Entity\ProduitVendu', 'pv', 'WITH', 'pv.vente = v')
            ->where('v.statusVente = :status')
            ->setParameter('status', 'paid');

        if ($debut) $qb->andWhere('v.venteDate >= :debut')->setParameter('debut', $debut->format('Y-m-d'));
        if ($fin)   $qb->andWhere('v.venteDate <= :fin')->setParameter('fin',   $fin->format('Y-m-d'));

        $result = $qb->getQuery()->getSingleResult();

        return [
            'FC'  => (float) ($result['fc'] ?? 0),
            'USD' => 0.0,
        ];
    }

    public function dernieres(int $limit = 5): array
    {
        return $this->createQueryBuilder('v')
            ->where('v.statusVente = :status')
            ->setParameter('status', 'paid')
            ->orderBy('v.venteDate', 'DESC')
            ->addOrderBy('v.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()->getResult();
    }


//    /**
//     * @return Vente[] Returns an array of Vente objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('v')
//            ->andWhere('v.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('v.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Vente
//    {
//        return $this->createQueryBuilder('v')
//            ->andWhere('v.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
