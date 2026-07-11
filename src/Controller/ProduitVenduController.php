<?php

namespace App\Controller;

use App\Entity\Produits;
use App\Entity\ProduitVendu;
use App\Entity\Vente;
use App\Form\ProduitVenduType;
use App\Repository\ProduitsRepository;
use App\Repository\ProduitVenduRepository;
use App\Repository\TauxRepository;
use App\Repository\VenteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/produit/vendu')]
class ProduitVenduController extends AbstractController
{
    #[Route('/', name: 'app_produit_vendu_index', methods: ['GET'])]
    public function index(Request $request, ProduitVenduRepository $produitVenduRepository): Response
    {
        $start = $request->query->get('start');
        $end = $request->query->get('end');

        $start = $start ? new \DateTime($start) : new \DateTime('-30 days');
        $end   = $end   ? new \DateTime($end)   : new \DateTime('today');

        $start->setTime(0, 0, 0);      // 00:00:00
        $end->setTime(23, 59, 59);
        //dd(new \DateTime('today'), $end==null);
        $produits = $produitVenduRepository->findByDateIntervalle($start, $end);
        return $this->render('produit_vendu/index.html.twig', [
            //'produit_vendus' => $produitVenduRepository->findBy([], ['createdAt' => 'DESC'] ),
            'produit_vendus' => $produits,
            'start' => $start,
            'end' => $end,

        ]);
    }

    #[Route('/jsonSaveLigneVente', name: 'jsonSaveLigneVente', methods: ['GET'])]
    public function jsonSaveLigneVente(Request $request,
                                  VenteRepository $venteRepository,
                                  ProduitsRepository $produitsRepository,
                                  EntityManagerInterface $entityManager,
                                    TauxRepository $tauxRepository
    ): Response
    {
        try {
            $tauxactif = $tauxRepository->findOneBy(['isActive' => true]);
            $produit = $produitsRepository->find($request->query->get('produitID'));
            $produitVendu = new ProduitVendu();
            $produitVendu->setCreatedAt(new \DateTimeImmutable());
            $produitVendu->setCreatedby($this->getUser()->getUsername());
            $produitVendu->setQty($request->query->get('qty'));
            $produitVendu->setVente($venteRepository->find($request->query->get('venteID')));
            $produitVendu->setProduit($produit);
            $produitVendu->setPrixUnitaire($produit->getPrix());
            //$produitVendu->setTaux($request->getSession()->get('tauxactif'));
            $produitVendu->setTaux($tauxactif->getCout());
            $entityManager->persist($produitVendu);
            $entityManager->flush();
            return new JsonResponse([
                'etat'=>true,
                'produitVenduID'=>$produitVendu->getId()
            ]);
        }catch (\Exception $e){
            return new JsonResponse([
                'etat'=>false
            ]);
        }

    }


    #[Route('/jsonDeleteLigneVente', name: 'jsonDeleteLigneVente', methods: ['GET'])]
    public function jsonDeleteLigneVente(Request $request,
                                       ProduitVenduRepository $produitVenduRepository,
                                       EntityManagerInterface $entityManager
    ): Response
    {
        try {
            $ligne = $produitVenduRepository->find($request->query->get('ligneID'));
            $entityManager->remove($ligne);
            $entityManager->flush();
            return new JsonResponse([
                'etat' => true
            ]);
        } catch (Exception $e) {
            return new JsonResponse([
                'etat' => false
            ]);
        }
    }

    #[Route('/new', name: 'app_produit_vendu_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $produitVendu = new ProduitVendu();
        $form = $this->createForm(ProduitVenduType::class, $produitVendu);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($produitVendu);
            $entityManager->flush();

            return $this->redirectToRoute('app_produit_vendu_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('produit_vendu/new.html.twig', [
            'produit_vendu' => $produitVendu,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_produit_vendu_show', methods: ['GET'])]
    public function show(ProduitVendu $produitVendu): Response
    {
        return $this->render('produit_vendu/show.html.twig', [
            'produit_vendu' => $produitVendu,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_produit_vendu_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, ProduitVendu $produitVendu, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ProduitVenduType::class, $produitVendu);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_produit_vendu_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('produit_vendu/edit.html.twig', [
            'produit_vendu' => $produitVendu,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_produit_vendu_delete', methods: ['POST'])]
    public function delete(Request $request, ProduitVendu $produitVendu, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$produitVendu->getId(), $request->request->get('_token'))) {
            $entityManager->remove($produitVendu);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_produit_vendu_index', [], Response::HTTP_SEE_OTHER);
    }
}
