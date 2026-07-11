<?php

namespace App\Controller;

use App\Repository\AuditLogRepository;
use App\Repository\CreditRepository;
use App\Repository\DebitRepository;
use App\Repository\EmployeRepository;
use App\Repository\PaieEmployeRepository;
use App\Repository\ProduitVenduRepository;
use App\Repository\VenteRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
    #[Route('/board', name: 'app_dashboard')]
    #[\Symfony\Component\Security\Http\Attribute\IsGranted('ROLE_ADMIN')]
    public function index(
        Request               $request,
        VenteRepository       $venteRepo,
        CreditRepository      $creditRepo,
        DebitRepository       $debitRepo,
        PaieEmployeRepository $paieEmployeRepo,
        ProduitVenduRepository $produitVenduRepo,
        EmployeRepository     $employeRepo,
        AuditLogRepository    $auditRepo,
    ): Response {
        // KPIs
        $caHier           = $venteRepo->caHier();
        $nbVentesHier     = $venteRepo->countHier();
        $caMois           = $venteRepo->caMoisCourant();
        $nbVentes         = $venteRepo->countMoisCourant();
        $masseSalariale   = $paieEmployeRepo->masseSalarialeMoisCourant();
        $totalCredits     = $creditRepo->totalMoisCourant();
        $totalDebits      = $debitRepo->totalMoisCourant();
        $nbEmployes       = $employeRepo->countAll();

        // Graphique CA 6 derniers mois (remplissage des mois sans ventes)
        $caParMoisRaw    = $venteRepo->caParMois(6);
        $caParMoisIndexe = [];
        foreach ($caParMoisRaw as $row) {
            $caParMoisIndexe[$row['mois']] = round((float)$row['montant'], 2);
        }
        $caLabels = [];
        $caData   = [];
        for ($i = 5; $i >= 0; $i--) {
            $mois = (new \DateTime("first day of -$i months"))->format('Y-m');
            $caLabels[] = $mois;
            $caData[]   = $caParMoisIndexe[$mois] ?? 0;
        }

        // Graphique ventes par jour du mois courant (remplissage des jours sans ventes)
        $joursRaw    = $venteRepo->ventesParJourDuMois();
        $joursIndexe = [];
        foreach ($joursRaw as $row) {
            $joursIndexe[(int)$row['jour']] = round((float)$row['montant'], 2);
        }
        $nbJoursMois = (int)(new \DateTime('last day of this month'))->format('j');
        $joursLabels = range(1, $nbJoursMois);
        $joursData   = array_map(fn($j) => $joursIndexe[$j] ?? 0, $joursLabels);

        // Top 5 produits (avec filtre de dates optionnel)
        $date1 = $request->query->get('date1');
        $date2 = $request->query->get('date2');
        $topDebut = $date1 ? \DateTimeImmutable::createFromFormat('Y-m-d', $date1) : null;
        $topFin   = $date2 ? \DateTimeImmutable::createFromFormat('Y-m-d', $date2) : null;
        $topProduits = $produitVenduRepo->topProduits(5, $topDebut, $topFin);

        // Tableaux récents
        $dernieresVentes    = $venteRepo->dernieres(5);
        $derniersLogs       = $auditRepo->findFiltered(null, null, null, null, null);
        $derniersLogs       = array_slice($derniersLogs, 0, 8);

        return $this->render('index.html.twig', [
            'caHier'           => $caHier,
            'nbVentesHier'     => $nbVentesHier,
            'caMois'           => $caMois,
            'nbVentes'         => $nbVentes,
            'masseSalariale'   => $masseSalariale,
            'totalCredits'     => $totalCredits,
            'totalDebits'      => $totalDebits,
            'nbEmployes'       => $nbEmployes,
            'caLabels'         => json_encode($caLabels),
            'caData'           => json_encode($caData),
            'joursLabels'      => json_encode($joursLabels),
            'joursData'        => json_encode($joursData),
            'topProduits'      => $topProduits,
            'topDate1'         => $date1,
            'topDate2'         => $date2,
            'dernieresVentes'  => $dernieresVentes,
            'derniersLogs'     => $derniersLogs,
            'moisCourant'      => (new \DateTime())->format('F Y'),
        ]);
    }
}
