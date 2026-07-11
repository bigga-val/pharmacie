<?php

namespace App\Twig;

use App\Service\AlerteService;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

class AlerteExtension extends AbstractExtension implements GlobalsInterface
{
    private ?array $cache = null;

    public function __construct(private AlerteService $alerteService) {}

    public function getGlobals(): array
    {
        // Calcul une seule fois par requête même si le template est inclus plusieurs fois
        if ($this->cache === null) {
            $this->cache = $this->alerteService->getAlertes();
        }

        return ['alertes' => $this->cache];
    }
}
