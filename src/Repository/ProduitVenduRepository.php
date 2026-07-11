<?php

namespace App\Repository;

use App\Entity\ProduitVendu;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ProduitVendu>
 *
 * @method ProduitVendu|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProduitVendu|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProduitVendu[]    findAll()
 * @method ProduitVendu[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProduitVenduRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProduitVendu::class);
    }

//    /**
//     * @return ProduitVendu[] Returns an array of ProduitVendu objects
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

    public function topProduits(int $limit = 5, ?\DateTimeInterface $debut = null, ?\DateTimeInterface $fin = null): array
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
            ->select('p.designation, SUM(pv.qty * pv.prixUnitaire) as chiffre, SUM(pv.qty) as quantite')
            ->from('App\Entity\ProduitVendu', 'pv')
            ->join('pv.produit', 'p')
            ->join('pv.vente', 'v')
            ->where('v.statusVente = :status')
            ->setParameter('status', 'paid')
            ->groupBy('p.id, p.designation')
            ->orderBy('chiffre', 'DESC')
            ->setMaxResults($limit);

        if ($debut) $qb->andWhere('v.venteDate >= :debut')->setParameter('debut', $debut->format('Y-m-d'));
        if ($fin)   $qb->andWhere('v.venteDate <= :fin')->setParameter('fin', $fin->format('Y-m-d'));

        return $qb->getQuery()->getResult();
    }

    /**
     * Calcule la marge bénéficiaire sur les ventes payées de la période.
     * CA   = SUM(qty × prixUnitaire)  [FC]
     * Coût = SUM(qty × coûtMoyenAchat) [FC], avec coûtMoyenAchat = SUM(cout)/SUM(qty) par produit
     */
    public function calculerMarge(?\DateTimeImmutable $debut, ?\DateTimeImmutable $fin): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $where  = "v.status_vente = 'paid'";
        $params = [];

        if ($debut) {
            $where .= ' AND v.vente_date >= :debut';
            $params['debut'] = $debut->format('Y-m-d');
        }
        if ($fin) {
            $where .= ' AND v.vente_date <= :fin';
            $params['fin'] = $fin->format('Y-m-d');
        }

        $sql = "
            SELECT
                COALESCE(SUM(pv.qty * pv.prix_unitaire), 0)                          AS chiffreAffaires,
                COALESCE(SUM(pv.qty * COALESCE(a_avg.cout_moyen, 0)), 0)              AS coutAchats
            FROM produit_vendu pv
            JOIN vente v        ON v.id = pv.vente_id
            LEFT JOIN (
                SELECT produit_id,
                       CASE WHEN SUM(qty) > 0 THEN SUM(cout) / SUM(qty) ELSE 0 END AS cout_moyen
                FROM   approvisionnement
                WHERE  type = 'approvisionnement' AND qty > 0
                GROUP  BY produit_id
            ) a_avg ON a_avg.produit_id = pv.produit_id
            WHERE $where
        ";

        $row = $conn->prepare($sql)->executeQuery($params)->fetchAssociative();

        $ca   = (float)($row['chiffreAffaires'] ?? 0);
        $cout = (float)($row['coutAchats']      ?? 0);
        $marge = $ca - $cout;

        return [
            'chiffreAffaires' => $ca,
            'coutAchats'      => $cout,
            'marge'           => $marge,
            'tauxMarge'       => $ca > 0 ? round($marge / $ca * 100, 1) : 0,
        ];
    }

    public function findByDateIntervalle($start, $end): array
    {


        return $this->createQueryBuilder('p')
            ->where('p.createdAt BETWEEN :start AND :end')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->orderBy('p.id', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

//    public function findOneBySomeField($value): ?ProduitVendu
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
