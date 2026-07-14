<?php

namespace App\Controller;

use App\Repository\CreditRepository;
use App\Repository\DebitRepository;
use App\Repository\PaieEmployeRepository;
use App\Repository\ProduitVenduRepository;
use App\Repository\VenteRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/caisse')]
#[IsGranted('ROLE_PHARMACIEN')]
class CaisseController extends AbstractController
{
    #[Route('/', name: 'app_caisse_index', methods: ['GET'])]
    public function index(
        Request                $request,
        VenteRepository        $venteRepo,
        DebitRepository        $debitRepo,
        CreditRepository       $creditRepo,
        PaieEmployeRepository  $paieRepo,
        ProduitVenduRepository $pvRepo,
    ): Response {
        $date1 = $request->get('date1');
        $date2 = $request->get('date2');

        $debut = $date1 ? \DateTimeImmutable::createFromFormat('Y-m-d', $date1) : null;
        $fin   = $date2 ? \DateTimeImmutable::createFromFormat('Y-m-d', $date2) : null;

        $ventes   = $venteRepo->totauxParPeriode($debut, $fin);
        $debits   = $debitRepo->totauxParPeriode($debut, $fin);
        $credits  = $creditRepo->totauxParPeriode($debut, $fin);
        $salaires = $paieRepo->totalParPeriode($debut, $fin);

        $totalEntreesFC  = $ventes['FC']  + $debits['FC'];
        $totalEntreesUSD = $ventes['USD'] + $debits['USD'];
        $totalSortiesFC  = $credits['FC'] + $salaires;
        $totalSortiesUSD = $credits['USD'];

        // Solde cumulé (tout l'historique, indépendant du filtre)
        $ventesTotal   = $venteRepo->totauxParPeriode(null, null);
        $debitsTotal   = $debitRepo->totauxParPeriode(null, null);
        $creditsTotal  = $creditRepo->totauxParPeriode(null, null);
        $salairesTotal = $paieRepo->totalParPeriode(null, null);
        $soldeGlobalFC  = ($ventesTotal['FC']  + $debitsTotal['FC'])  - ($creditsTotal['FC']  + $salairesTotal);
        $soldeGlobalUSD = ($ventesTotal['USD'] + $debitsTotal['USD']) - $creditsTotal['USD'];

        // Marge bénéficiaire sur les ventes payées de la période
        $marge = $pvRepo->calculerMarge($debut, $fin);

        return $this->render('caisse/index.html.twig', [
            'debut'           => $debut,
            'fin'             => $fin,
            'ventes'          => $ventes,
            'debits'          => $debits,
            'credits'         => $credits,
            'salaires'        => $salaires,
            'totalEntreesFC'  => $totalEntreesFC,
            'totalEntreesUSD' => $totalEntreesUSD,
            'totalSortiesFC'  => $totalSortiesFC,
            'totalSortiesUSD' => $totalSortiesUSD,
            'soldeFC'         => $totalEntreesFC  - $totalSortiesFC,
            'soldeUSD'        => $totalEntreesUSD - $totalSortiesUSD,
            'soldeGlobalFC'   => $soldeGlobalFC,
            'soldeGlobalUSD'  => $soldeGlobalUSD,
            'marge'           => $marge,
        ]);
    }
}
