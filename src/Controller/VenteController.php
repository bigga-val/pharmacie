<?php

namespace App\Controller;

use App\Entity\ProduitVendu;
use App\Entity\Vente;
use App\Entity\Credit;
use App\Form\VenteType;
use App\Repository\TauxRepository;
use App\Service\PdfService;
use App\Repository\ApprovisionnementRepository;
use App\Repository\ProduitsRepository;
use App\Repository\ProduitVenduRepository;
use App\Repository\VenteRepository;
use App\Repository\CreditRepository;
use App\Service\FPdfGenerator;
use Doctrine\DBAL\Driver\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Dompdf\Dompdf;



#[Route('/vente')]
class VenteController extends AbstractController
{
    #[Route('/', name: 'app_vente_index', methods: ['GET'])]
    public function index(VenteRepository $venteRepository): Response
    {
        return $this->render('vente/index.html.twig', [
            'ventes' => $venteRepository->venteTotalGrouped(),
        ]);
    }



    #[Route('/jsonSaveVente', name: 'jsonSaveVente', methods: ['GET'])]
    public function jsonSaveVente(Request $request,
                                  VenteRepository $venteRepository,
                                  EntityManagerInterface $entityManager,
    ): Response
    {
        try {
            $vente = new Vente();
            $countVente = count($venteRepository->findAll());
            $vente->setCreatedBy($this->getUser()->getUsername());
            $vente->setVenteDate(new \DateTime());
            $vente->setCreatedAt(new \DateTimeImmutable());
            $vente->setStatusVente("progress");
            $vente->setNumeroVente($this->genererNUmeroVente(5, $countVente));

            $entityManager->persist($vente);
            $entityManager->flush();
            return new JsonResponse([
                'etat'=>true,
                'venteID'=>$vente->getId()
            ]);
        }catch (Exception $e){
            return new JsonResponse([
                'etat'=>false
            ]);
        }

    }


    #[Route('/jsonSaveVentePay', name: 'jsonSaveVentePay', methods: ['GET'])]
    public function jsonSaveVentePay(Request $request,
                                     VenteRepository $venteRepository,
                                     EntityManagerInterface $entityManager,
    ): Response
    {
        try {
            $vente = new Vente();
            $countVente = count($venteRepository->findAll());
            $vente->setCreatedBy($this->getUser()->getUsername());
            $vente->setVenteDate(new \DateTime());
            $vente->setCreatedAt(new \DateTimeImmutable());
            $vente->setStatusVente("paid");
            $vente->setNumeroVente($this->genererNUmeroVente(5, $countVente));
            $entityManager->persist($vente);
            $entityManager->flush();
            return new JsonResponse([
                'etat'=>true,
                'venteID'=>$vente->getId()
            ]);
        }catch (\Exception $e){
            return new JsonResponse([
                'etat'=>false
            ]);
        }

    }



    #[Route('/jsonGetMonthlyVente', name: 'jsonGetMonthlyVente', methods: ['GET'])]
    public function jsonGetMonthlyVente(Request $request,
                                        VenteRepository $venteRepository,
                                        EntityManagerInterface $entityManager
    ): Response
    {
        try {
            $ventes=$venteRepository->venteAnnuelle();
            return new JsonResponse([
                'etat'=>true,
                'ventes'=>$ventes
            ]);
        }catch (\Exception $e){
            return new JsonResponse([
                'etat'=>false
            ]);
        }

    }


    function genererNUmeroVente($sequenceLength, $lastId) {
        // Définir le préfixe
        $prefix = "V";
        $sequenceLength = $sequenceLength;
        $nextId = $lastId + 1;
        $formattedSequence = str_pad($nextId, $sequenceLength, "0", STR_PAD_LEFT);
        $nomenclature = $prefix . $formattedSequence;
        return $nomenclature;
    }

    #[Route('/print/{id}', name:'app_vente_print', methods: ['GET'])]
    public function print(Vente $vente,
                          FPdfGenerator $pdfGenerator,
                          VenteRepository $venteRepository,
                          ProduitVenduRepository $produitVenduRepository
    ):Response {
        //$this->testDirectPrint();
        $pdfContent = $this->generatePdfAction($vente, $pdfGenerator,
            $venteRepository,
            $produitVenduRepository);

        //return $this->redirectToRoute('app_facture_show', ['id'=>$facture->getId()], Response::HTTP_SEE_OTHER);
        return new Response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Attachement'=>false
            //'Content-Disposition' => 'attachment; filename="votre_fichier.pdf"',
        ]);
    }


    #[Route('/generatePdf/{id}', name: 'app_generate_pdf', methods: ['GET'])]
    public function generatePdfAction(Vente $vente,
                                      FPdfGenerator $pdfGenerator,
                                      VenteRepository $venteRepository,
                                      ProduitVenduRepository $produitVenduRepository ): Response
    {
        $pdfContent = $pdfGenerator->generateInvoicePdf($vente->getId(), $venteRepository, $produitVenduRepository);
        //dd($pdfContent);

        //return $this->redirectToRoute('app_facture_show', ['id'=>$facture->getId()], Response::HTTP_SEE_OTHER);
        return new Response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Attachement'=>false
            //'Content-Disposition' => 'attachment; filename="votre_fichier.pdf"',
        ]);
    }

    #[Route('/jsonGetProduct2', name: 'jsonGetProduct2', methods: ['GET'])]
    public function jsonGetProduct2(Request $request, ApprovisionnementRepository $approvisionnementRepository,): Response
    {
        $prodStock = $approvisionnementRepository->stockProduitByID($request->query->get('productID'));
        return new JsonResponse([
            'data'=>$prodStock
//            'prix'=>$prodStock['prix'],
//            'unite'=>$prodStock['mesure'],
//            'min'=>$prodStock['min'],
//            'max'=>$prodStock['max'],
//            'reel'=>$prodStock['dispo'],
        ]);
    }

    #[Route('/jsonGetProductCommande', name: 'jsonGetProductCommande', methods: ['GET'])]
    public function jsonGetProductCommande(Request $request, ApprovisionnementRepository $approvisionnementRepository,): Response
    {
        try{
            $prodStock = $approvisionnementRepository->stockProduitCommandeByID($request->query->get('productID'));
            return new JsonResponse([
                'data'=>$prodStock,
                'etat'=>false
            ]);
        }catch (Exception $e){
            return new JsonResponse([
                'data'=>[],
                'etat'=>false
            ]);
        }

    }

    #[Route('/new', name: 'app_vente_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager,
                        ProduitsRepository $produitsRepository,
                        VenteRepository $venteRepository,
                        ApprovisionnementRepository $approvisionnementRepository,
                        TauxRepository $tauxRepository,
    ): Response
    {
        $venteNo = $this->genererNUmeroVente( 5, count($venteRepository->findAll()));
        $produits = $approvisionnementRepository->stockProduit();
        $tauxactif = $tauxRepository->findOneBy(['isActive' => true]);
        $request->getSession()->set('tauxactif', $tauxactif->getCout());
        return $this->renderForm('vente/new.html.twig', [
            'produits' => $produits,
            'numeroFacture'=> $venteNo,
        ]);
    }

    #[Route('/finance', name: 'app_vente_finance', methods: ['GET'])]
    public function finance( ProduitVenduRepository $produitVenduRepository,
                             VenteRepository $venteRepository,
                             ApprovisionnementRepository $approvisionnementRepository,
                             CreditRepository $creditRepo
    ): Response
    {
        ///===== Today activities =======
        $leoVente = $venteRepository->venteparDate(new \DateTime('today'));
        $leoVente = $leoVente ? $leoVente[0]['montant']:0;
        $djanaVente = $venteRepository->venteparDate(new \DateTime('yesterday'));
        $djanaVente = $djanaVente ? $djanaVente[0]['montant']:0;
        $leoPercent = 0;

        if($djanaVente > 0){
            $leoPercent = (($leoVente - $djanaVente) * 100) / $djanaVente;
        }
        //--- Sortie du jour de caisse ---\\

        $leoCaisse = $creditRepo->sortiepardate(new \DateTime('today'), new \DateTime('today'));
        $leoCaisse = $leoCaisse ? $leoCaisse[0]['montant']:0;
        $djanaCaisse = $creditRepo->sortiepardate(new \DateTime('yesterday'), new \DateTime('yesterday'));
        $djanaCaisse = $djanaCaisse ? $djanaCaisse[0]['montant']:0;
        $leocaissePercent = 0;
        if($djanaCaisse > 0){
            $leocaissePercent = (($leoCaisse - $djanaCaisse) * 100) / $djanaCaisse;
        }
        ///=============== End today Activities  ==================
        //============ weekly activities ====
//        $this->getWeekDates('2024/04/05');
        $today = new \DateTime('today');

// Semaine en cours
        $thisSunday   = $this->getWeekDates($today)['sunday'];
        $thisSaturday = $this->getWeekDates($today)['saturday'];
        $thisWeekData = $venteRepository->venteparIntervale2($thisSunday, $thisSaturday);
        $thisWeek     = $thisWeekData ? $thisWeekData[0]['montant'] : 0;

// Semaine dernière (clone pour éviter de modifier $today)
        $lastWeekDate  = (clone $today)->modify('-7 days');
        $lastSunday    = $this->getWeekDates($lastWeekDate)['sunday'];
        $lastSaturday  = $this->getWeekDates($lastWeekDate)['saturday'];
        $lastWeekData  = $venteRepository->venteparIntervale2($lastSunday, $lastSaturday);
        $lastWeek      = $lastWeekData ? $lastWeekData[0]['montant'] : 0;
        //dd($lastSunday, $lastSaturday, $lastWeek, $thisWeek);

        $weekPercent = 0;
        if($lastWeek > 0){
            $weekPercent = (($thisWeek - $lastWeek) * 100) / $lastWeek;
        }

        // ----- Sortie de caisse de la semaine --//
        $thisWeekCaisse = $creditRepo->sortiepardate($thisSunday, $thisSaturday)[0]['montant'] ?? 0;
        $lastWeekCaisse = $creditRepo->sortiepardate($lastSunday, $lastSaturday)[0]['montant'] ?? 0;
        $percentWeekCaisse = 0;
        if($lastWeekCaisse > 0){
            $percentWeekCaisse = (($thisWeekCaisse - $lastWeekCaisse) * 100) / $lastWeekCaisse;
        }
//        dd($thisWeekCaisse, $lastWeekCaisse, $percentWeekCaisse, $lastSunday, $lastSaturday);
        ///======== End week intervale ====== \\\
///
///
///======= Monthly activities   =======\\\\
        $today2 = new \DateTime('today');
        $firstdayMonth = (clone $today2)->modify('first day of this month');
        $lastdayMonth  = (clone $today2)->modify('last day of this month');
        $fisrtDaylastMonth = (clone $today2)->modify('first day of last month');
        $lastdayLastMonth  = (clone $today2)->modify('last day of last month');

        $currentMonth = $venteRepository->venteparIntervale2($firstdayMonth, $lastdayMonth)[0]['montant'] ?? 0;
        $lastMonth    = $venteRepository->venteparIntervale2($fisrtDaylastMonth, $lastdayLastMonth)[0]['montant'] ?? 0;
        $monthPercent = 0;
        if($lastMonth > 0){
            $monthPercent = (($currentMonth - $lastMonth) * 100) / $lastMonth;
        }
        ///==== caisse
        $currentMonthCaisse = $creditRepo->sortiepardate($firstdayMonth, $lastdayMonth)[0]['montant'] ?? 0;
        $lastMonthCaisse    = $creditRepo->sortiepardate($fisrtDaylastMonth, $lastdayLastMonth)[0]['montant'] ?? 0;
        $monthCaissePercent = 0;
        if($lastMonthCaisse > 0){
            $monthCaissePercent = (($currentMonthCaisse - $lastMonthCaisse) * 100) / $lastMonthCaisse;
        }
        ///=== End monthly activities
        ///
        ///====== Yearly activities =======

        $date1 = new \DateTime('today');
        $date2 = new \DateTime('today');
        $firstdayYear = $date1->setDate($date1->format('Y'), 1, 1);
        $lastdayYear =$date2->setDate($date2->format('Y'), 12, 31);
        $currentYear = $venteRepository->venteparIntervale2($firstdayYear, $lastdayYear)[0]['montant']?$venteRepository->venteparIntervale2($firstdayYear, $lastdayYear)[0]['montant']:0;
        //dd($currentYear, $firstdayYear, $lastdayYear);
        $date_1 = new \DateTime('today');
        $date_2 = new \DateTime('today');
        $firstdaylastYear = $date_1->setDate($date1->format('Y'), 1, 1)->modify('-1 year');
        $lastdaylastYear =$date_2->setDate($date2->format('Y'), 12, 31)->modify('-1 year');
        $lastYear = $venteRepository->venteparIntervale2($firstdaylastYear, $lastdaylastYear)[0]['montant']?$venteRepository->venteparIntervale2($firstdaylastYear, $lastdaylastYear)[0]['montant']:0;

        $yearPercent = 0;
        if($lastYear > 0){
            $yearPercent = (($currentYear - $lastYear) * 100) / $lastYear;
        }
        //-- sorties caisse
        $currentYearCaisse = $creditRepo->sortiepardate($firstdayYear, $lastdayYear)[0]['montant'] ?? 0;
        $lastYearCaisse    = $creditRepo->sortiepardate($firstdaylastYear, $lastdaylastYear)[0]['montant'] ?? 0;
        $yearPercentCaisse = 0;
        if($lastYearCaisse > 0){
            $yearPercentCaisse = (($currentYearCaisse - $lastYearCaisse) * 100) / $lastYearCaisse;
        }
        //dd($currentYearCaisse, $lastYearCaisse, $firstdayYear, $lastdayYear, $lastYear, $currentYear);
//======= End yearly activities =====\\\\\\\\
        //dd($myYear->format('o'), $myYear->modify('-1 year'));

        return $this->render('vente/finance.html.twig', [
            "leoVente"=> $leoVente,
            "djanaVente"=> $djanaVente,
            "leoPercent"=> $leoPercent,
            "thisWeek" => $thisWeek,
            "lastWeek"=>$lastWeek,
            "weekPercent"=>$weekPercent,
            "currentMonth"=>$currentMonth,
            "lastMonth"=>$lastMonth,
            "percentMonth"=>$monthPercent,
            "currentYear"=>$currentYear,
            "lastYear"=>$lastYear,
            "percentYear"=>$yearPercent,
            //--sortie caisse
            "leoCaisse"=>$leoCaisse,
            "djanaCaisse"=>$djanaCaisse,
            "leocaissePercent"=>$leocaissePercent,
            "thisWeekCaisse"=>$thisWeekCaisse,
            "lastWeekCaisse"=>$lastWeekCaisse,
            "percentWeekCaisse"=>$percentWeekCaisse,
            //"currentMonth"=>$currentMonth,
            //"lastMonth"=>$lastMonth,
            //"monthPercent"=>$monthPercent,
            //"monthPercent"=>$monthPercent,
            "currentMonthCaisse"=>$currentMonthCaisse,
            "lastMonthCaisse"=>$lastMonthCaisse,
            "monthCaissePercent"=>$monthCaissePercent,
            "currentYearCaisse"=>$currentYearCaisse,
            "lastYearCaisse"=>$lastYearCaisse,
            "yearPercentCaisse"=>$yearPercentCaisse

        ]);
    }


    // function permettant de retrouver la date de debut de semaine et de fin de semaine.
    function getWeekDates($date) {
        //$date = '2024-04-24';
        $date = $date->format('Y').'-'.$date->format('m').'-'.$date->format('d');
        $dateTime1 = new \DateTime($date);
        $dateTime2 = new \DateTime($date);
        // Déterminer le jour de la semaine (0 = dimanche)
        $dayOfWeek = $dateTime1->format('w');
        // Déterminer le décalage pour obtenir le samedi
        $saturdayOffset = 6 - $dayOfWeek;

        // Calculer la date du samedi
        $saturday = $dateTime1->modify('+'.$saturdayOffset.' days');
        //dd($saturday);
        // Déterminer le décalage pour obtenir le dimanche
        $sundayOffset = -$dayOfWeek;
        // dd($sundayOffset);


        // Calculer la date du dimanche
        $sunday = $dateTime2->modify('+'.$sundayOffset.' days');

        // Calculer la date du samedi
        //$saturday = $dateTime->modify('+'.$saturdayOffset.' days');
        //dd($sunday, $saturday);

        return array(
            'sunday' => $sunday->format('Y-m-d'),
            'saturday' => $saturday->format('Y-m-d'),
        );
    }



    #[Route('/{id}', name: 'app_vente_show', methods: ['GET'])]
    public function show(Vente $vente,
                         ProduitVenduRepository $produitVenduRepository,
                         ApprovisionnementRepository $approvisionnementRepository): Response
    {
        $produits = $approvisionnementRepository->stockProduit();
        return $this->render('vente/show.html.twig', [
            'vente' => $vente,
            'produitsVendu'=> $produitVenduRepository->findBy(['vente'=>$vente->getId()]),
            'produits'=>$produits,
        ]);
    }


    #[Route('/cancel/{id}', name: 'app_vente_cancel', methods: ['GET'])]
    public function cancel(Vente $vente,  ProduitVenduRepository $produitVenduRepository, EntityManagerInterface $entityManager): Response
    {
        $vente->setStatusVente('canceled');
        $entityManager->flush();

        return $this->redirectToRoute('app_vente_show', ['id'=>$vente->getId()], Response::HTTP_SEE_OTHER);

    }

    #[Route('/confirm/{id}', name: 'app_vente_confirm', methods: ['GET'])]
    public function confirm(Vente $vente,  ProduitVenduRepository $produitVenduRepository, EntityManagerInterface $entityManager): Response
    {
        $vente->setStatusVente('paid');
        $entityManager->flush();

        return $this->redirectToRoute('app_vente_show', ['id'=>$vente->getId()], Response::HTTP_SEE_OTHER);

    }

    #[Route('/{id}/edit', name: 'app_vente_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Vente $vente, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(VenteType::class, $vente);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_vente_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('vente/edit.html.twig', [
            'vente' => $vente,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_vente_delete', methods: ['POST'])]
    public function delete(Request $request, Vente $vente, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$vente->getId(), $request->request->get('_token'))) {
            $entityManager->remove($vente);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_vente_index', [], Response::HTTP_SEE_OTHER);
    }
}