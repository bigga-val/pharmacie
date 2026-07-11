<?php

namespace App\Controller;

use App\Entity\Debit;
use App\Entity\Vente;
use App\Form\DebitType;
use App\Repository\DebitRepository;
use App\Repository\ProduitVenduRepository;
use App\Repository\TauxRepository;
use App\Repository\VenteRepository;
use App\Service\FPdfGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/debit')]
class DebitController extends AbstractController
{
    #[Route('/', name: 'app_debit_index', methods: ['GET'])]
    public function index(DebitRepository $debitRepository, Request $request): Response
    {
        $start = $request->query->get('start');
        $end = $request->query->get('end');

        $start = $start ? new \DateTime($start) : new \DateTime('today');
        $end   = $end   ? new \DateTime($end)   : new \DateTime('today');

        $start->setTime(0, 0, 0);      // 00:00:00
        $end->setTime(23, 59, 59);

        return $this->render('debit/index.html.twig', [
            'debits' => $debitRepository->findDebitByDatesIntervalle($start, $end),
            'start' => $start,
            'end' => $end,

        ]);
    }

    #[Route('/new', name: 'app_debit_new', methods: ['GET', 'POST'])]
    public function new(Request $request,
                        EntityManagerInterface $entityManager,
                        DebitRepository $debitRepository,
                        TauxRepository $tauxRepository
    ): Response
    {

        $tauxactif = $tauxRepository->findOneBy(['isActive' => true]);
        $request->getSession()->set('tauxactif', $tauxactif->getCout());
        if ($request->getMethod() == "POST") {
            $data = $request->request->all();
            $debit = new Debit();
            $debit->setMontant($data["montant"]);
            $debit->setRaison($data["raison"]);
            $debit->setDevise($data["devise"]);
            $debit->setCreatedBy($this->getUser()->getUserIdentifier());
            $debit->setCreatedAt(new \DateTimeImmutable());
            $debit->setDateDebit(new \DateTime());
            $debit->setTaux($request->getSession()->get('tauxactif'));

            $entityManager->persist($debit);
            $this->addFlash('success', "Sortie de caisse enregistrée avec succès");

//            dd($debit);
            $entityManager->flush();
        }

        return $this->renderForm('debit/new.html.twig', [
            'debits' => $debitRepository->findBy([], ['createdAt' => 'DESC'], 5),
        ]);
    }

    #[Route('/debisPdf/{id}', name: 'app_devis_pdf', methods: ['GET'])]
    public function debisPdf(Debit $debit,
                                      FPdfGenerator $pdfGenerator,
                                      DebitRepository $debitRepository ): Response
    {
        $pdfContent = $pdfGenerator->generateVersementPdf($debit->getId(), $debitRepository);
        //dd($pdfContent);

        //return $this->redirectToRoute('app_facture_show', ['id'=>$facture->getId()], Response::HTTP_SEE_OTHER);
        return new Response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Attachement'=>false
            //'Content-Disposition' => 'attachment; filename="votre_fichier.pdf"',
        ]);
    }

    #[Route('/{id}', name: 'app_debit_show', methods: ['GET'])]
    public function show(Debit $debit): Response
    {
        return $this->render('debit/show.html.twig', [
            'debit' => $debit,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_debit_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Debit $debit, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(DebitType::class, $debit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_debit_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('debit/edit.html.twig', [
            'debit' => $debit,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_debit_delete', methods: ['POST'])]
    public function delete(Request $request, Debit $debit, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$debit->getId(), $request->request->get('_token'))) {
            $entityManager->remove($debit);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_debit_index', [], Response::HTTP_SEE_OTHER);
    }
}
