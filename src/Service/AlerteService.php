<?php

namespace App\Service;

use App\Repository\AlerteConfigRepository;
use App\Repository\ApprovisionnementRepository;
use App\Repository\ProduitsRepository;

class AlerteService
{
    public function __construct(
        private AlerteConfigRepository      $alerteConfigRepository,
        private ApprovisionnementRepository $approvisionnementRepository,
        private ProduitsRepository          $produitsRepository,
    ) {}

    /**
     * Retourne toutes les alertes actives selon la configuration.
     *
     * @return array{
     *     stockBas: array,
     *     peremptions: array,
     *     total: int,
     *     config: \App\Entity\AlerteConfig
     * }
     */
    public function getAlertes(): array
    {
        $config = $this->alerteConfigRepository->getConfig();

        $stockBas    = [];
        $peremptions = [];

        if ($config->isActifStockBas()) {
            $stockBas = $this->approvisionnementRepository->findProduitsStockBas();
        }

        if ($config->isActifPeremption()) {
            $produits = $this->produitsRepository->findByExpirationAlert(
                $config->getJoursAvantPeremption()
            );

            $today = new \DateTime('today');

            foreach ($produits as $produit) {
                $diff          = $today->diff($produit->getPreemption());
                $joursRestants = (int) $diff->format('%r%a'); // négatif si dépassé

                $peremptions[] = [
                    'produit'       => $produit,
                    'joursRestants' => $joursRestants,
                    'expire'        => $joursRestants < 0,
                ];
            }
        }

        return [
            'stockBas'    => $stockBas,
            'peremptions' => $peremptions,
            'total'       => count($stockBas) + count($peremptions),
            'config'      => $config,
        ];
    }
}
