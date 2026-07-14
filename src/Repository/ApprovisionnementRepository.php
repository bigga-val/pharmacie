<?php

namespace App\Repository;

use App\Entity\Approvisionnement;
use App\Entity\Vente;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;


/**
 * @extends ServiceEntityRepository<Approvisionnement>
 *
 * @method Approvisionnement|null find($id, $lockMode = null, $lockVersion = null)
 * @method Approvisionnement|null findOneBy(array $criteria, array $orderBy = null)
 * @method Approvisionnement[]    findAll()
 * @method Approvisionnement[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ApprovisionnementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Approvisionnement::class);
    }

    public function stockProduit(): array{
        $em = $this->getEntityManager();
        $query = $em->createQuery(
            '    
            SELECT p.id produitID, p.code, concat(p.designation, \' - \', UPPER(p.code)) as designation , p.prix, p.preemption, p.fabricant,
                  SUM(e.qty) AS totalEntree, p.minimum,
                  (
                    SELECT 
                      SUM(s.qty) AS quantite_sortie
                    FROM App\Entity\Produits pr, App\Entity\ProduitVendu s, App\Entity\Vente v
                     where s.produit = pr.id
                          and v.id = s.vente
                          and v.statusVente = \'paid\'
                          and pr.id = produitID
                    GROUP BY pr.id 
                    ) as totalSortie,
                    (
                    SELECT 
                      SUM(s2.qty) AS quantite_reserve
                    FROM App\Entity\Produits pr2, App\Entity\ProduitVendu s2, App\Entity\Vente v2
                     where s2.produit = pr2.id
                          and v2.id = s2.vente
                          and v2.statusVente = \'progress\'
                          and pr2.id = produitID
                    GROUP BY pr2.id 
                    ) as totalReserve,
                    SUM(e.cout) cout
            FROM App\Entity\Produits p, App\Entity\Approvisionnement e
            where e.produit = p.id
            GROUP BY p.id
            '
        );
        return $query->getResult();

    }

    public function stockProduitByDate(
        ?\DateTimeInterface $dateDebut = null,
        ?\DateTimeInterface $dateFin = null
    ): array {
        $em = $this->getEntityManager();

        $query = $em->createQuery(
            '
        SELECT
            p.id                                                        AS produitID,
            p.code,
            CONCAT(p.designation, \' - \', UPPER(p.code))              AS designation,
            p.prix,
            p.preemption,
            p.fabricant,
            p.minimum,

            -- Entrées dans la période
            COALESCE(SUM(e.qty),  0)                                    AS totalEntree,
            COALESCE(SUM(e.cout), 0)                                    AS cout,

            -- Coût moyen unitaire (toutes entrées historiques, hors filtre date)
            (
                SELECT COALESCE(SUM(eH.cout), 0)
                FROM App\Entity\Approvisionnement eH
                WHERE eH.produit = p
            )                                                           AS coutHistoTotal,
            (
                SELECT COALESCE(SUM(eH2.qty), 0)
                FROM App\Entity\Approvisionnement eH2
                WHERE eH2.produit = p
            )                                                           AS qtyHistoTotal,

            -- Stock avant la période (toutes les entrées avant dateDebut)
            (
                SELECT COALESCE(SUM(e2.qty), 0)
                FROM App\Entity\Approvisionnement e2
                WHERE e2.produit = p
                  AND (:dateDebut IS NULL OR e2.createdAt < :dateDebut)
            )                                                           AS stockAvant,

            -- Sorties dans la période (ventes payées)
            (
                SELECT COALESCE(SUM(sv.qty), 0)
                FROM App\Entity\ProduitVendu sv
                JOIN sv.vente v
                WHERE sv.produit = p
                  AND v.statusVente = :paid
                  AND (:dateDebut IS NULL OR v.createdAt >= :dateDebut)
                  AND (:dateFin   IS NULL OR v.createdAt <= :dateFin)
            )                                                           AS totalSortie,

            -- Réservations dans la période (ventes en cours)
            (
                SELECT COALESCE(SUM(sr.qty), 0)
                FROM App\Entity\ProduitVendu sr
                JOIN sr.vente vr
                WHERE sr.produit = p
                  AND vr.statusVente = :progress
                  AND (:dateDebut IS NULL OR vr.createdAt >= :dateDebut)
                  AND (:dateFin   IS NULL OR vr.createdAt <= :dateFin)
            )                                                           AS totalReserve

        FROM App\Entity\Produits p
        LEFT JOIN App\Entity\Approvisionnement e WITH e.produit = p
            AND (:dateDebut IS NULL OR e.createdAt >= :dateDebut)
            AND (:dateFin   IS NULL OR e.createdAt <= :dateFin)

        GROUP BY
            p.id, p.code, p.designation, p.prix,
            p.preemption, p.fabricant, p.minimum
        '
        )
            ->setParameter('paid',      'paid')
            ->setParameter('progress',  'progress')
            ->setParameter('dateDebut', $dateDebut)
            ->setParameter('dateFin',   $dateFin);

        $results = $query->getResult();

        // Stock disponible = stock avant la période + entrées - sorties - réserves
        $results = array_map(function ($row) use ($dateDebut) {
            // Quand aucune date de début n'est fournie, stockAvant couvre toutes les entrées
            // (même période que totalEntree) → ne pas le cumuler pour éviter le double-comptage
            $stockDisponible = $dateDebut === null
                ? ($row['totalEntree'] - $row['totalSortie'] - $row['totalReserve'])
                : ($row['stockAvant'] + $row['totalEntree'] - $row['totalSortie'] - $row['totalReserve']);

            $cmu = $row['qtyHistoTotal'] > 0
                ? ($row['coutHistoTotal'] / $row['qtyHistoTotal'])
                : 0;

            $row['stockDisponible'] = $stockDisponible;
            $row['coutStockDispo']  = round($cmu * max($stockDisponible, 0), 2);
            $row['stockBas']        = $stockDisponible <= $row['minimum'] ? 1 : 0;
            return $row;
        }, $results);

        usort($results, function ($a, $b) {
            if ($b['stockBas'] !== $a['stockBas']) {
                return $b['stockBas'] <=> $a['stockBas'];
            }
            return $a['designation'] <=> $b['designation'];
        });

        return $results;
    }

    public function stockProduitByID($prodID): array{
        $em = $this->getEntityManager();
        $query = $em->createQuery(
            '    
            SELECT p.id produitID, p.code, concat(p.designation, \' - \', UPPER(p.code)) as designation ,
             p.prix, p.uniteMesure, p.minimum, p.maximum, p.preemption, p.fabricant,
                  SUM(e.qty) AS totalEntree,
                  (
                    SELECT 
                      SUM(s.qty) AS quantite_sortie
                    FROM App\Entity\Produits pr, App\Entity\ProduitVendu s, App\Entity\Vente v
                     where s.produit = pr.id
                          and v.id = s.vente
                          and v.statusVente = \'paid\'
                          and pr.id = :prodID
                    GROUP BY pr.id 
                    ) as totalSortie,
                    (
                    SELECT 
                      SUM(s2.qty) AS quantite_reserve
                    FROM App\Entity\Produits pr2, App\Entity\ProduitVendu s2, App\Entity\Vente v2
                     where s2.produit = pr2.id
                          and v2.id = s2.vente
                          and v2.statusVente = \'progress\'
                          and pr2.id = :prodID
                    GROUP BY pr2.id 
                    ) as totalReserve
            FROM App\Entity\Produits p, App\Entity\Approvisionnement e
            where e.produit = p.id
            and p.id = :prodID
            GROUP BY p.id
            '
        );
        $query->setParameter('prodID', $prodID);

        return $query->getResult();

    }


    public function stockProduitCommandeByID($prodID): array{
        $em = $this->getEntityManager();
        $query = $em->createQuery(
            '    
            SELECT p.id produitID, p.code, concat(p.designation, \' - \', UPPER(p.code)) as designation ,
             p.prix, p.uniteMesure, p.minimum, p.maximum, p.preemption, p.fabricant,
                    p.prix prixTotal
            FROM App\Entity\Produits p,
            App\Entity\CategorieProduit cp
            where p.Categorie = cp.id
            and p.id = :prodID
            GROUP BY p.id
            '
        );
        $query->setParameter('prodID', $prodID);

        return $query->getResult();

    }


    public function findApprosGrouped(): array{
        $em = $this->getEntityManager();
        $query = $em->createQuery(
            '
            select SUM(a.qty) as totalentre, p.id, p.designation, p.fabricant
                from App\Entity\Approvisionnement a, App\Entity\Produits p
                
                where a.produit = p.id
                group by p.id   
            '
        );
        return $query->getResult();
    }

    public function approparDate($mydate): array{
        $em = $this->getEntityManager();
        $query = $em->createQuery(
            'SELECT SUM(a.cout) montant, SUM(a.cout / a.taux) convert
                    FROM App\Entity\Approvisionnement a
                     where a.approDate = :mydate
                    GROUP BY a.approDate
            '
        );
        //$query->setParameter('mydate', $mydate);
        $query->setParameter('mydate', $mydate->format('Y-m-d'));
        return $query->getResult();

    }


    /**
     * Retourne les produits dont le stock disponible actuel est <= minimum.
     * Réutilise stockProduitByDate() sans filtre de date.
     */
    public function findProduitsStockBas(): array
    {
        $all = $this->stockProduitByDate();
        return array_values(array_filter($all, fn($r) => $r['stockBas'] === 1));
    }

//    /**
//     * @return Approvisionnement[] Returns an array of Approvisionnement objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('a.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Approvisionnement
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
