<?php

namespace App\Controller;

use App\Entity\PaieEmploye;
use App\Form\PaieEmployeType;
use App\Repository\PaieEmployeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/paie/employe')]
class PaieEmployeController extends AbstractController
{
    #[Route('/', name: 'app_paie_employe_index', methods: ['GET'])]
    public function index(PaieEmployeRepository $paieEmployeRepository): Response
    {
        return $this->render('paie_employe/index.html.twig', [
            'paie_employes' => $paieEmployeRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_paie_employe_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $paieEmploye = new PaieEmploye();
        $form = $this->createForm(PaieEmployeType::class, $paieEmploye);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($paieEmploye);
            $entityManager->flush();

            return $this->redirectToRoute('app_paie_employe_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('paie_employe/new.html.twig', [
            'paie_employe' => $paieEmploye,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_paie_employe_show', methods: ['GET'])]
    public function show(PaieEmploye $paieEmploye): Response
    {
        return $this->render('paie_employe/show.html.twig', [
            'paie_employe' => $paieEmploye,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_paie_employe_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, PaieEmploye $paieEmploye, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(PaieEmployeType::class, $paieEmploye);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_paie_employe_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('paie_employe/edit.html.twig', [
            'paie_employe' => $paieEmploye,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_paie_employe_delete', methods: ['POST'])]
    public function delete(Request $request, PaieEmploye $paieEmploye, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$paieEmploye->getId(), $request->request->get('_token'))) {
            $entityManager->remove($paieEmploye);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_paie_employe_index', [], Response::HTTP_SEE_OTHER);
    }
}
