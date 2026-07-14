<?php

namespace App\Controller;

use App\Entity\CategorieProduit;
use App\Form\CategorieProduitType;
use App\Repository\CategorieProduitRepository;
use App\Repository\ProduitsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/categorie/produit')]
#[IsGranted('ROLE_PHARMACIEN')]
class CategorieProduitController extends AbstractController
{
    #[Route('/', name: 'app_categorie_produit_index', methods: ['GET', 'POST'])]
    public function index(CategorieProduitRepository $categorieProduitRepository,
                          Request $request, EntityManagerInterface $entityManager
    ): Response
    {
        $categorieProduit = new CategorieProduit();
        $form = $this->createForm(CategorieProduitType::class, $categorieProduit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($categorieProduit);
            $entityManager->flush();

            //return $this->redirectToRoute('app_categorie_produit_index', [], Response::HTTP_SEE_OTHER);
        }
        return $this->renderForm('categorie_produit/index.html.twig', [
            'categorie_produits' => $categorieProduitRepository->findAll(),
            'categorie_produit' => $categorieProduit,
            'form' => $form,
        ]);
    }

    #[Route('/new', name: 'app_categorie_produit_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $categorieProduit = new CategorieProduit();
        $form = $this->createForm(CategorieProduitType::class, $categorieProduit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($categorieProduit);
            $entityManager->flush();

            return $this->redirectToRoute('app_categorie_produit_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('categorie_produit/new.html.twig', [
            'categorie_produit' => $categorieProduit,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_categorie_produit_show', methods: ['GET'])]
    public function show(CategorieProduit $categorieProduit): Response
    {
        return $this->render('categorie_produit/show.html.twig', [
            'categorie_produit' => $categorieProduit,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_categorie_produit_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, CategorieProduit $categorieProduit, EntityManagerInterface $entityManager, ProduitsRepository $produitsRepository): Response
    {
        $form = $this->createForm(CategorieProduitType::class, $categorieProduit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $pourcentage = $categorieProduit->getPourcentage() ?? 0;
            foreach ($produitsRepository->findBy(['Categorie' => $categorieProduit]) as $produit) {
                $produit->setPrix(($produit->getPrixAchat() ?? 0) * (1 + $pourcentage / 100));
            }
            $entityManager->flush();

            return $this->redirectToRoute('app_categorie_produit_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('categorie_produit/edit.html.twig', [
            'categorie_produit' => $categorieProduit,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_categorie_produit_delete', methods: ['POST'])]
    public function delete(Request $request, CategorieProduit $categorieProduit, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$categorieProduit->getId(), $request->request->get('_token'))) {
            $entityManager->remove($categorieProduit);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_categorie_produit_index', [], Response::HTTP_SEE_OTHER);
    }
}
