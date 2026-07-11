<?php

namespace App\Controller;

use App\Entity\Credit;
use App\Form\CreditType;
use App\Repository\CreditRepository;
use App\Repository\TauxRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/credit')]
class CreditController extends AbstractController
{
    #[Route('/', name: 'app_credit_index', methods: ['GET'])]
    public function index(CreditRepository $creditRepository): Response
    {
        return $this->render('credit/index.html.twig', [
            'credits' => $creditRepository->findBy([], ['createdAt' => 'DESC']),
        ]);
    }

    #[Route('/new', name: 'app_credit_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager,
                        CreditRepository $creditRepository,
                        TauxRepository $tauxRepository
    ): Response
    {

        $tauxactif = $tauxRepository->findOneBy(['isActive' => true]);
        $request->getSession()->set('tauxactif', $tauxactif->getCout());
        if ($request->getMethod() == "POST") {
            $data = $request->request->all();
            $credit = new Credit();
            $credit->setRaison($data["raison"]);
            $credit->setMontant($data["montant"]);
            $credit->setDevise($data["devise"] ?? 'FC');
            $credit->setCreatedBy($this->getUser()->getUserIdentifier());
            $credit->setCreatedAt(new \DateTimeImmutable());
            $credit->setDateCredit(new \DateTime());
            $credit->setTaux($request->getSession()->get('tauxactif'));

            $entityManager->persist($credit);
            $this->addFlash('success', "Sortie de caisse enregistrée avec succès");

//            dd($credit);
            $entityManager->flush();
        }


        return $this->renderForm('credit/new.html.twig', [
            'credits' => $creditRepository->findBy([], ['createdAt' => 'DESC']),
        ]);
    }

    #[Route('/{id}', name: 'app_credit_show', methods: ['GET'])]
    public function show(Credit $credit): Response
    {
        return $this->render('credit/show.html.twig', [
            'credit' => $credit,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_credit_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Credit $credit, EntityManagerInterface $entityManager): Response
    {
//        $form = $this->createForm(CreditType::class, $credit);
//        $form->handleRequest($request);

//        if ($form->isSubmitted() && $form->isValid()) {
//            $entityManager->flush();
//
//            return $this->redirectToRoute('app_credit_index', [], Response::HTTP_SEE_OTHER);
//        }
        if ($request->getMethod() == "POST") {
            $data = $request->request->all();
            // $credit = new Credit();
            $credit->setRaison($data["raison"]);
            $credit->setMontant($data["montant"]);
            $credit->setDevise($data["devise"] ?? 'FC');
            $credit->setCreatedBy($this->getUser()->getUserIdentifier());
//            $credit->setCreatedAt(new \DateTimeImmutable());
            $credit->setDateCredit(new \DateTime());
            $entityManager->persist($credit);
            $this->addFlash('success', "Sortie de caisse modifiée avec succès");

//            dd($credit);
            $entityManager->flush();
        }
        return $this->renderForm('credit/edit.html.twig', [
            'credit' => $credit,
        ]);
    }

    #[Route('/{id}', name: 'app_credit_delete', methods: ['POST'])]
    public function delete(Request $request, Credit $credit, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$credit->getId(), $request->request->get('_token'))) {
            $entityManager->remove($credit);
            $entityManager->flush();
            $this->addFlash('success', "Sortie de caisse supprimée avec succès");
        }
        return $this->redirectToRoute('app_credit_index', [], Response::HTTP_SEE_OTHER);
    }
}
