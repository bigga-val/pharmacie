<?php

namespace App\Controller;

use App\Entity\Taux;
use App\Form\TauxType;
use App\Repository\TauxRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/taux')]
class TauxController extends AbstractController
{
    #[Route('/', name: 'app_taux_index', methods: ['GET'])]
    public function index(TauxRepository $tauxRepository): Response
    {
        return $this->render('taux/index.html.twig', [
            'tauxes' => $tauxRepository->findAll(),
        ]);
    }

    #[Route('/saveChangedTaux', name: 'app_taux_new_changed', methods: ['GET', 'POST'])]
    public function saveChangedTaux(Request $request, TauxRepository $tauxRepository, EntityManagerInterface $entityManager): Response
    {
        $tauxes = $tauxRepository->findAll();

        foreach($tauxes as $taux){
            $taux->setIsActive(false);
        }

        $taux = new Taux();
        $cout = $request->get('taux');
        $taux->setUser($this->getUser());
        $taux->setIsActive(true);
        $taux->setCout($cout);
        $taux->setCreatedAt(new \DateTimeImmutable());

        $entityManager->persist($taux);
        $entityManager->flush();
        $request->getSession()->set('tauxactif', $cout);
        return $this->redirectToRoute('app_taux_index');
    }

    #[Route('/new', name: 'app_taux_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $taux = new Taux();
        $form = $this->createForm(TauxType::class, $taux);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($taux);
            $entityManager->flush();

            return $this->redirectToRoute('app_taux_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('taux/new.html.twig', [
            'taux' => $taux,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_taux_show', methods: ['GET'])]
    public function show(Taux $taux): Response
    {
        return $this->render('taux/show.html.twig', [
            'taux' => $taux,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_taux_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Taux $taux, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(TauxType::class, $taux);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_taux_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('taux/edit.html.twig', [
            'taux' => $taux,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_taux_delete', methods: ['POST'])]
    public function delete(Request $request, Taux $taux, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$taux->getId(), $request->request->get('_token'))) {
            $entityManager->remove($taux);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_taux_index', [], Response::HTTP_SEE_OTHER);
    }
}
