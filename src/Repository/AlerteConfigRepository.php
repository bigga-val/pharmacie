<?php

namespace App\Repository;

use App\Entity\AlerteConfig;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AlerteConfig>
 */
class AlerteConfigRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, AlerteConfig::class);
        $this->em = $em;
    }

    /**
     * Retourne la configuration unique des alertes.
     * Si aucune config n'existe, en crée une avec les valeurs par défaut.
     */
    public function getConfig(): AlerteConfig
    {
        $config = $this->findOneBy([]);

        if ($config === null) {
            $config = new AlerteConfig();
            $this->em->persist($config);
            $this->em->flush();
        }

        return $config;
    }
}
