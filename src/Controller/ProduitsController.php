<?php

namespace App\Controller;

use App\Entity\Produits;
use App\Form\ProduitsType;
use App\Repository\ApprovisionnementRepository;
use App\Repository\ProduitVenduRepository;
use App\Repository\ProduitsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/produits')]
class ProduitsController extends AbstractController
{
    #[Route('/', name: 'app_produits_index', methods: ['GET'])]
    public function index(ProduitsRepository $produitsRepository): Response
    {
        return $this->render('produits/index.html.twig', [
            'produits' => $produitsRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_produits_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        ProduitsRepository $produitsRepository,
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

            $entityManager->persist($produit);
            $entityManager->flush();
            $this->addFlash('success', "Produit créé avec succès");

            return $this->redirectToRoute('app_produits_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('produits/new.html.twig', [
            'produit' => $produit,
            'form'    => $form,
            'code'    => $code,
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



    #[Route('/{id}/edit', name: 'app_produits_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        Produits $produit,
        EntityManagerInterface $entityManager,
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

            $entityManager->flush();
            $this->addFlash('success', "Produit modifié avec succès");
            return $this->redirectToRoute('app_produits_show', ['id' => $produit->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('produits/edit.html.twig', [
            'produit' => $produit,
            'form'    => $form,
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
