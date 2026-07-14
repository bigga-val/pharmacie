<?php

namespace App\Controller;

use App\Entity\Produits;
use App\Form\ProduitsType;
use App\Repository\ApprovisionnementRepository;
use App\Repository\CategorieProduitRepository;
use App\Repository\ProduitVenduRepository;
use App\Repository\ProduitsRepository;
use App\Service\ImportProduitsService;
use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/produits')]
#[IsGranted('ROLE_INFIRMIERE')]
class ProduitsController extends AbstractController
{
    #[Route('/', name: 'app_produits_index', methods: ['GET'])]
    public function index(ProduitsRepository $produitsRepository): Response
    {
        return $this->render('produits/index.html.twig', [
            'produits' => $produitsRepository->findAll(),
        ]);
    }

    #[IsGranted('ROLE_PHARMACIEN')]
    #[Route('/new', name: 'app_produits_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        ProduitsRepository $produitsRepository,
        CategorieProduitRepository $catRepo,
    ): Response {
        $imagesDir = $this->getParameter('produits_images_directory');
        $produit = new Produits();
        $code    = $this->genererCodeProduit(6, count($produitsRepository->findAll()));
        $form    = $this->createForm(ProduitsType::class, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('imageFile')->getData();
            if ($imageFile) {
                $filename = uniqid() . '.' . $imageFile->guessExtension();
                if (!is_dir($imagesDir)) {
                    mkdir($imagesDir, 0755, true);
                }
                $imageFile->move($imagesDir, $filename);
                $produit->setImage($filename);
            }

            $prixAchat    = $produit->getPrixAchat() ?? 0;
            $pourcentage  = $produit->getCategorie()?->getPourcentage() ?? 0;
            $produit->setPrix($prixAchat * (1 + $pourcentage / 100));

            $entityManager->persist($produit);
            $entityManager->flush();
            $this->addFlash('success', "Produit créé avec succès");

            return $this->redirectToRoute('app_produits_index', [], Response::HTTP_SEE_OTHER);
        }

        $categoriesMap = [];
        foreach ($catRepo->findAll() as $cat) {
            $categoriesMap[$cat->getId()] = $cat->getPourcentage() ?? 0;
        }

        return $this->renderForm('produits/new.html.twig', [
            'produit'       => $produit,
            'form'          => $form,
            'code'          => $code,
            'categoriesMap' => $categoriesMap,
        ]);
    }

    #[IsGranted('ROLE_PHARMACIEN')]
    #[Route('/import/template', name: 'app_produits_import_template', methods: ['GET'])]
    public function importTemplate(CategorieProduitRepository $catRepo): StreamedResponse
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Produits');

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
            $sheet->setCellValue('B1', 'AFYA — Modèle d\'import Produits');
            $sheet->getStyle('B1')->getFont()->setBold(true)->setSize(13);
            $headersRow = 3;
        } else {
            $headersRow = 1;
        }

        // En-têtes
        $headers = [
            'A' => 'designation *',
            'B' => 'code *',
            'C' => 'categorie *',
            'D' => 'prixAchat *',
            'E' => 'uniteMesure',
            'F' => 'minimum',
            'G' => 'maximum',
            'H' => 'fabricant',
            'I' => 'preemption (YYYY-MM-DD)',
        ];
        foreach ($headers as $col => $value) {
            $sheet->setCellValue($col . $headersRow, $value);
        }

        // Style en-tête
        $sheet->getStyle('A'.$headersRow.':I'.$headersRow)->getFont()->setBold(true);
        $sheet->getStyle('A'.$headersRow.':D'.$headersRow)->getFont()->getColor()->setRGB('CC0000');

        $exRow = $headersRow + 1;
        $sheet->setCellValue('A'.$exRow, 'Amoxicilline 500mg');
        $sheet->setCellValue('B'.$exRow, 'AMX500');
        $sheet->setCellValue('C'.$exRow, 'Antibiotiques');
        $sheet->setCellValue('D'.$exRow, 560);
        $sheet->setCellValue('E'.$exRow, 'cp');
        $sheet->setCellValue('F'.$exRow, 50);
        $sheet->setCellValue('G'.$exRow, 200);
        $sheet->setCellValue('H'.$exRow, 'Beecham');
        $sheet->setCellValue('I'.$exRow, '2027-12-31');

        // Largeurs colonnes
        foreach (range('A', 'I') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Feuille catégories disponibles
        $catSheet = $spreadsheet->createSheet();
        $catSheet->setTitle('Catégories disponibles');
        $catSheet->setCellValue('A1', 'Catégories en base');
        $catSheet->getStyle('A1')->getFont()->setBold(true);
        foreach ($catRepo->findAll() as $i => $cat) {
            $catSheet->setCellValue('A' . ($i + 2), $cat->getDesignation() . ' (' . $cat->getPourcentage() . '%)');
        }
        $catSheet->getColumnDimension('A')->setAutoSize(true);

        $spreadsheet->setActiveSheetIndex(0);

        $writer = new Xlsx($spreadsheet);
        $response = new StreamedResponse(function () use ($writer) {
            $writer->save('php://output');
        });
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment; filename="modele_import_produits.xlsx"');
        return $response;
    }

    #[IsGranted('ROLE_PHARMACIEN')]
    #[Route('/import', name: 'app_produits_import', methods: ['GET', 'POST'])]
    public function import(Request $request, ImportProduitsService $importService): Response
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
                        $tmpPath = $file->getPathname();
                        $rapport = $importService->import($tmpPath);
                    } catch (\Exception $e) {
                        $this->addFlash('danger', 'Erreur lors de la lecture du fichier : ' . $e->getMessage());
                    }
                }
            }
        }

        return $this->render('produits/import.html.twig', [
            'rapport' => $rapport,
        ]);
    }

    #[Route('/{id}', name: 'app_produits_show', methods: ['GET'])]
    public function show(
        Produits $produit,
        ApprovisionnementRepository $approRepo,
        ProduitVenduRepository $produitVenduRepo,
    ): Response {
        $stockData = $approRepo->stockProduitByID($produit->getId());
        $stock     = $stockData[0] ?? null;

        $dernieresEntrees = $approRepo->findBy(
            ['produit' => $produit],
            ['createdAt' => 'DESC'],
            8
        );

        $dernieresSorties = $produitVenduRepo->findBy(
            ['produit' => $produit],
            ['createdAt' => 'DESC'],
            8
        );

        return $this->render('produits/show.html.twig', [
            'produit'          => $produit,
            'stock'            => $stock,
            'dernieresEntrees' => $dernieresEntrees,
            'dernieresSorties' => $dernieresSorties,
        ]);
    }


    #[Route('/jsonGetProduct2', name: 'jsonGetProduct2', methods: ['GET'])]
    public function jsonGetProduct2(Request $request): Response
    {
        return new JsonResponse([
            'etat' => true,
        ]);
    }



    #[IsGranted('ROLE_PHARMACIEN')]
    #[Route('/{id}/edit', name: 'app_produits_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        Produits $produit,
        EntityManagerInterface $entityManager,
        CategorieProduitRepository $catRepo,
    ): Response {
        $imagesDir = $this->getParameter('produits_images_directory');
        $form = $this->createForm(ProduitsType::class, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('imageFile')->getData();
            if ($imageFile) {
                // Supprimer l'ancienne image si elle existe
                if ($produit->getImage()) {
                    $oldFile = $imagesDir . '/' . $produit->getImage();
                    if (file_exists($oldFile)) {
                        unlink($oldFile);
                    }
                }
                $filename = uniqid() . '.' . $imageFile->guessExtension();
                if (!is_dir($imagesDir)) {
                    mkdir($imagesDir, 0755, true);
                }
                $imageFile->move($imagesDir, $filename);
                $produit->setImage($filename);
            }

            $prixAchat   = $produit->getPrixAchat() ?? 0;
            $pourcentage = $produit->getCategorie()?->getPourcentage() ?? 0;
            $produit->setPrix($prixAchat * (1 + $pourcentage / 100));

            $entityManager->flush();
            $this->addFlash('success', "Produit modifié avec succès");
            return $this->redirectToRoute('app_produits_show', ['id' => $produit->getId()], Response::HTTP_SEE_OTHER);
        }

        $categoriesMap = [];
        foreach ($catRepo->findAll() as $cat) {
            $categoriesMap[$cat->getId()] = $cat->getPourcentage() ?? 0;
        }

        return $this->renderForm('produits/edit.html.twig', [
            'produit'       => $produit,
            'form'          => $form,
            'categoriesMap' => $categoriesMap,
        ]);
    }

    function genererCodeProduit($sequenceLength, $lastId) {
        // Définir le préfixe
        $prefix = "CD";
        $sequenceLength = $sequenceLength;
        $nextId = $lastId + 1;
        $formattedSequence = str_pad($nextId, $sequenceLength, "0", STR_PAD_LEFT);
        $nomenclature = $prefix . $formattedSequence;
        return $nomenclature;
    }

    #[IsGranted('ROLE_PHARMACIEN')]
    #[Route('/{id}', name: 'app_produits_delete', methods: ['POST'])]
    public function delete(Request $request, Produits $produit, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$produit->getId(), $request->request->get('_token'))) {
            $entityManager->remove($produit);
            $entityManager->flush();
            $this->addFlash('success', "Produit supprimé avec succès");

        }

        return $this->redirectToRoute('app_produits_index', [], Response::HTTP_SEE_OTHER);
    }
}
