<?php

namespace App\Controller;

use App\Entity\Approvisionnement;
use App\Form\AjustementStockType;
use App\Form\ApprovisionnementType;
use App\Repository\ApprovisionnementRepository;
use App\Repository\ProduitsRepository;
use App\Repository\TauxRepository;
use App\Service\ImportApprovisionnementService;
use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/approvisionnement')]
#[IsGranted('ROLE_INFIRMIERE')]
class ApprovisionnementController extends AbstractController
{
    #[IsGranted('ROLE_PHARMACIEN')]
    #[Route('/', name: 'app_approvisionnement_index', methods: ['GET'])]
    public function index(ApprovisionnementRepository $approvisionnementRepository): Response
    {
        return $this->render('approvisionnement/index.html.twig', [
            'approvisionnements' => $approvisionnementRepository->findBy([], ['createdAt' => 'DESC']),
        ]);
    }

    #[Route('/stock', name: 'app_approvisionnement_stock', methods: ['GET'])]
    public function stock(ApprovisionnementRepository $approvisionnementRepository, Request $request): Response
    {
        //$appros = $approvisionnementRepository->stockProduit();
        $date1 = $request->get('date1');
        $date2 = $request->get('date2');
        $dateDebut = $date1 ? \DateTimeImmutable::createFromFormat('Y-m-d', $date1) : null;
        $dateFin   = $date2 ? \DateTimeImmutable::createFromFormat('Y-m-d', $date2) : null;

//        dd([
//            'date1_raw'  => $date1,
//            'date2_raw'  => $date2,
//            'dateDebut'  => $dateDebut,
//            'dateFin'    => $dateFin,
//        ]);
        //$appros = $approvisionnementRepository->stockProduit(new \DateTime('2025-01-01'), new \DateTime('2025-12-31'));
        $appros = $approvisionnementRepository->stockProduitByDate(
            $dateDebut,
            $dateFin
        );

        return $this->render('approvisionnement/stock.html.twig', [
            'appros'=>$appros
        ]);
    }

    #[Route('/ajustement', name: 'app_approvisionnement_ajustement', methods: ['GET', 'POST'])]
    public function ajustement(Request $request, EntityManagerInterface $entityManager, TauxRepository $tauxRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $approvisionnement = new Approvisionnement();
        $form = $this->createForm(AjustementStockType::class, $approvisionnement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $tauxActif = $tauxRepository->findOneBy(['isActive' => true]);
            $approvisionnement->setType('ajustement');
            $approvisionnement->setApproDate(new \DateTime());
            $approvisionnement->setCreatedAt(new \DateTimeImmutable());
            $approvisionnement->setCreatedBy($this->getUser()->getUserIdentifier());
            $approvisionnement->setTaux($tauxActif?->getCout());
            $approvisionnement->setCout(0);

            $entityManager->persist($approvisionnement);
            $entityManager->flush();
            $this->addFlash('success', 'Ajustement de stock enregistré avec succès');

            return $this->redirectToRoute('app_approvisionnement_stock', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('approvisionnement/ajustement.html.twig', [
            'form' => $form,
        ]);
    }

    #[IsGranted('ROLE_PHARMACIEN')]
    #[Route('/new', name: 'app_approvisionnement_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, ProduitsRepository $produitsRepository, TauxRepository $tauxRepository): Response
    {
        $approvisionnement = new Approvisionnement();
        $form = $this->createForm(ApprovisionnementType::class, $approvisionnement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $tauxActif = $tauxRepository->findOneBy(['isActive' => true]);
            $prixUnitaire = (float) ($form->get('prixUnitaire')->getData() ?? $approvisionnement->getProduit()?->getPrixAchat() ?? 0);
            $approvisionnement->setType('approvisionnement');
            $approvisionnement->setApproDate(new \DateTime());
            $approvisionnement->setCreatedAt(new \DateTimeImmutable());
            $approvisionnement->setCreatedBy($this->getUser()->getUserIdentifier());
            $approvisionnement->setTaux($tauxActif?->getCout());
            $approvisionnement->setCout(($approvisionnement->getQty() ?? 0) * $prixUnitaire);

            $entityManager->persist($approvisionnement);
            $entityManager->flush();
            $this->addFlash('success', "Produit approvisionné avec succès");

            return $this->redirectToRoute('app_approvisionnement_index', [], Response::HTTP_SEE_OTHER);
        }

        $prixMap = [];
        foreach ($produitsRepository->findAll() as $p) {
            $prixMap[$p->getId()] = $p->getPrixAchat();
        }

        return $this->renderForm('approvisionnement/new.html.twig', [
            'approvisionnement' => $approvisionnement,
            'prixMap'           => $prixMap,
            'form' => $form,
        ]);
    }

    #[IsGranted('ROLE_PHARMACIEN')]
    #[Route('/import/template', name: 'app_approvisionnement_import_template', methods: ['GET'])]
    public function importTemplate(ProduitsRepository $produitsRepo): StreamedResponse
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Approvisionnement');

        // Logo
        $logoPath = dirname(__DIR__, 2) . '/public/assets/images/afya.png';
        if (file_exists($logoPath)) {
            $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
            $drawing->setPath($logoPath);
            $drawing->setHeight(45);
            $drawing->setCoordinates('A1');
            $drawing->setWorksheet($sheet);
            $sheet->getRowDimension(1)->setRowHeight(40);
            $sheet->getRowDimension(2)->setRowHeight(20);
            $sheet->setCellValue('B1', 'AFYA — Modèle d\'import Approvisionnement');
            $sheet->getStyle('B1')->getFont()->setBold(true)->setSize(13);
            $headersRow = 3;
        } else {
            $headersRow = 1;
        }

        $headers = [
            'A' => 'code_produit *',
            'B' => 'quantite *',
            'C' => 'prixUnitaire (optionnel)',
            'D' => 'date (YYYY-MM-DD, optionnel)',
        ];
        foreach ($headers as $col => $value) {
            $sheet->setCellValue($col . $headersRow, $value);
        }

        $sheet->getStyle('A'.$headersRow.':D'.$headersRow)->getFont()->setBold(true);
        $sheet->getStyle('A'.$headersRow.':B'.$headersRow)->getFont()->getColor()->setRGB('CC0000');

        foreach (['A', 'B', 'C', 'D'] as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $exRow = $headersRow + 1;
        $sheet->setCellValue('A'.$exRow, 'AMX500');
        $sheet->setCellValue('B'.$exRow, 100);
        $sheet->setCellValue('C'.$exRow, 560);
        $sheet->setCellValue('D'.$exRow, date('Y-m-d'));

        // Feuille produits disponibles
        $prodSheet = $spreadsheet->createSheet();
        $prodSheet->setTitle('Produits disponibles');
        $prodSheet->setCellValue('A1', 'Code');
        $prodSheet->setCellValue('B1', 'Désignation');
        $prodSheet->setCellValue('C1', 'Prix achat');
        $prodSheet->getStyle('A1:C1')->getFont()->setBold(true);
        foreach ($produitsRepo->findBy([], ['designation' => 'ASC']) as $i => $p) {
            $prodSheet->setCellValue('A' . ($i + 2), $p->getCode());
            $prodSheet->setCellValue('B' . ($i + 2), $p->getDesignation());
            $prodSheet->setCellValue('C' . ($i + 2), $p->getPrixAchat());
        }
        foreach (['A', 'B', 'C'] as $col) {
            $prodSheet->getColumnDimension($col)->setAutoSize(true);
        }

        $spreadsheet->setActiveSheetIndex(0);

        $writer = new Xlsx($spreadsheet);
        $response = new StreamedResponse(function () use ($writer) {
            $writer->save('php://output');
        });
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment; filename="modele_import_approvisionnement.xlsx"');
        return $response;
    }

    #[IsGranted('ROLE_PHARMACIEN')]
    #[Route('/import', name: 'app_approvisionnement_import', methods: ['GET', 'POST'])]
    public function import(Request $request, ImportApprovisionnementService $importService): Response
    {
        $rapport = null;

        if ($request->isMethod('POST')) {
            $file = $request->files->get('fichier');

            if (!$file) {
                $this->addFlash('danger', 'Aucun fichier sélectionné.');
            } else {
                $ext = strtolower($file->getClientOriginalExtension());
                if (!in_array($ext, ['xlsx', 'xls'])) {
                    $this->addFlash('danger', 'Format invalide. Utilisez un fichier .xlsx ou .xls');
                } else {
                    try {
                        $rapport = $importService->import(
                            $file->getPathname(),
                            $this->getUser()->getUserIdentifier()
                        );
                    } catch (\Exception $e) {
                        $this->addFlash('danger', 'Erreur lors de la lecture du fichier : ' . $e->getMessage());
                    }
                }
            }
        }

        return $this->render('approvisionnement/import.html.twig', [
            'rapport' => $rapport,
        ]);
    }

    #[Route('/{id}', name: 'app_approvisionnement_show', methods: ['GET'])]
    public function show(Approvisionnement $approvisionnement): Response
    {
        return $this->render('approvisionnement/show.html.twig', [
            'approvisionnement' => $approvisionnement,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_approvisionnement_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Approvisionnement $approvisionnement, EntityManagerInterface $entityManager): Response
    {
        if (in_array('ROLE_ADMIN', $this->getUser()->getRoles(), true)) {
            // User is admin, display admin-specific content
            $form = $this->createForm(ApprovisionnementType::class, $approvisionnement);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $prixUnitaire = (float) ($form->get('prixUnitaire')->getData() ?? $approvisionnement->getProduit()?->getPrixAchat() ?? 0);
                $approvisionnement->setCout(($approvisionnement->getQty() ?? 0) * $prixUnitaire);
                $entityManager->flush();
                $this->addFlash('success', "Modifications prises en charge avec succès");

                return $this->redirectToRoute('app_approvisionnement_index', [], Response::HTTP_SEE_OTHER);
            }

            $prixMap = [];
            foreach ($entityManager->getRepository(\App\Entity\Produits::class)->findAll() as $p) {
                $prixMap[$p->getId()] = $p->getPrixAchat();
            }

            return $this->renderForm('approvisionnement/edit.html.twig', [
                'approvisionnement' => $approvisionnement,
                'prixMap'           => $prixMap,
                'form' => $form,
            ]);
        } else {
            throw $this->createAccessDeniedException();
        }

    }

    #[Route('/{id}', name: 'app_approvisionnement_delete', methods: ['POST'])]
    public function delete(Request $request, Approvisionnement $approvisionnement, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$approvisionnement->getId(), $request->request->get('_token'))) {
            $this->addFlash('success',  "Suppression effectuée avec succès");

            $entityManager->remove($approvisionnement);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_approvisionnement_index', [], Response::HTTP_SEE_OTHER);
    }
}
