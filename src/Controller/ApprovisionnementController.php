<?php

namespace App\Controller;

use App\Entity\Approvisionnement;
use App\Form\AjustementStockType;
use App\Form\ApprovisionnementType;
use App\Repository\ApprovisionnementRepository;
use App\Repository\ProduitsRepository;
use App\Repository\TauxRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/approvisionnement')]
class ApprovisionnementController extends AbstractController
{
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

    #[Route('/new', name: 'app_approvisionnement_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, ProduitsRepository $produitsRepository, TauxRepository $tauxRepository): Response
    {
        $approvisionnement = new Approvisionnement();
        $form = $this->createForm(ApprovisionnementType::class, $approvisionnement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $tauxActif = $tauxRepository->findOneBy(['isActive' => true]);
            $prixUnitaire = (float) ($form->get('prixUnitaire')->getData() ?? $approvisionnement->getProduit()?->getPrix() ?? 0);
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
            $prixMap[$p->getId()] = $p->getPrix();
        }

        return $this->renderForm('approvisionnement/new.html.twig', [
            'approvisionnement' => $approvisionnement,
            'prixMap'           => $prixMap,
            'form' => $form,
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
                $prixUnitaire = (float) ($form->get('prixUnitaire')->getData() ?? $approvisionnement->getProduit()?->getPrix() ?? 0);
                $approvisionnement->setCout(($approvisionnement->getQty() ?? 0) * $prixUnitaire);
                $entityManager->flush();
                $this->addFlash('success', "Modifications prises en charge avec succès");

                return $this->redirectToRoute('app_approvisionnement_index', [], Response::HTTP_SEE_OTHER);
            }

            $prixMap = [];
            foreach ($entityManager->getRepository(\App\Entity\Produits::class)->findAll() as $p) {
                $prixMap[$p->getId()] = $p->getPrix();
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
