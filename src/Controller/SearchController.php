<?php

namespace App\Controller;

use App\Repository\EmployeRepository;
use App\Repository\ProduitsRepository;
use App\Repository\VenteRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class SearchController extends AbstractController
{
    #[Route('/search', name: 'app_search', methods: ['GET'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function index(
        Request            $request,
        ProduitsRepository $produitsRepo,
        VenteRepository    $venteRepo,
        EmployeRepository  $employeRepo,
    ): Response {
        $q = trim($request->query->get('q', ''));

        if (strlen($q) < 2) {
            return $this->render('search/results.html.twig', [
                'q'        => $q,
                'produits' => [],
                'ventes'   => [],
                'employes' => [],
                'total'    => 0,
            ]);
        }

        // Normalise les numéros de facture : V0001223, V01223, V1223 → V01223
        $venteQ = $q;
        if (preg_match('/^v(\d+)$/i', $q, $matches)) {
            $num    = (int) $matches[1];
            $venteQ = 'V' . str_pad($num, 5, '0', STR_PAD_LEFT);
        }

        $produits = $produitsRepo->search($q);
        $ventes   = $venteRepo->search($venteQ);
        $employes = $employeRepo->search($q);

        return $this->render('search/results.html.twig', [
            'q'        => $q,
            'produits' => $produits,
            'ventes'   => $ventes,
            'employes' => $employes,
            'total'    => count($produits) + count($ventes) + count($employes),
        ]);
    }
}
