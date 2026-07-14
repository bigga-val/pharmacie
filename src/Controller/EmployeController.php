<?php

namespace App\Controller;

use App\Entity\Employe;
use App\Entity\Paie;
use App\Entity\PaieEmploye;
use App\Form\EmployeType;
use App\Repository\EmployeRepository;
use App\Repository\PaieEmployeRepository;
use App\Repository\PaieRepository;
use App\Service\FPdfGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/employe')]
#[IsGranted('ROLE_ADMIN')]
class EmployeController extends AbstractController
{
    #[Route('/', name: 'app_employe_index', methods: ['GET'])]
    public function index(EmployeRepository $employeRepository): Response
    {
        return $this->render('employe/index.html.twig', [
            'employes' => $employeRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_employe_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $employe = new Employe();
        $form = $this->createForm(EmployeType::class, $employe);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($employe);
            $entityManager->flush();

            return $this->redirectToRoute('app_employe_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('employe/new.html.twig', [
            'employe' => $employe,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_employe_show', methods: ['GET'])]
    public function show(Employe $employe): Response
    {
        return $this->render('employe/show.html.twig', [
            'employe' => $employe,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_employe_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Employe $employe, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(EmployeType::class, $employe);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_employe_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('employe/edit.html.twig', [
            'employe' => $employe,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/payer', name: 'app_employe_payer', methods: ['GET', 'POST'])]
    public function payer(Request $request,
                          Employe $employe,
                          PaieRepository $paieRepository,
                          PaieEmployeRepository $paieEmployeRepository,
                          EntityManagerInterface $entityManager,
    ): Response
    {
        $periodes = $paieRepository->findAll();
        $paiesEmploye = $paieEmployeRepository->findBy(['Employe' => $employe], ['createdAt' => 'DESC']);

        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('payer'.$employe->getId(), $request->request->get('_token'))) {
                $this->addFlash('error', 'Token invalide.');
                return $this->redirectToRoute('app_employe_payer', ['id' => $employe->getId()]);
            }

            $periode = $paieRepository->find($request->request->get('periode'));
            if (!$periode) {
                $this->addFlash('error', 'Période de paie introuvable.');
                return $this->redirectToRoute('app_employe_payer', ['id' => $employe->getId()]);
            }

            // Bloquer les doublons
            $existant = $paieEmployeRepository->findOneBy(['Employe' => $employe, 'Paie' => $periode]);
            if ($existant) {
                $this->addFlash('error', "Cet employé a déjà été payé pour la période : {$periode->getLabel()}.");
                return $this->redirectToRoute('app_employe_payer', ['id' => $employe->getId()]);
            }

            $nbJours = (int) $request->request->get('nb_jours');
            $primes = (float) $request->request->get('primes', 0);
            $deductions = (float) $request->request->get('deductions', 0);
            $salaireBase = ($employe->getSalaireJournalier() ?? 0) * $nbJours;

            $paieEmploye = new PaieEmploye();
            $paieEmploye->setEmploye($employe);
            $paieEmploye->setPaie($periode);
            $paieEmploye->setNbJours($nbJours);
            $paieEmploye->setSalaireBase($salaireBase);
            $paieEmploye->setPrimes($primes);
            $paieEmploye->setDeductions($deductions);
            $paieEmploye->calculerTotal();
            $paieEmploye->setCreatedAt(new \DateTimeImmutable('now'));

            $entityManager->persist($paieEmploye);
            $entityManager->flush();

            $this->addFlash('success', "Paie enregistrée avec succès pour {$employe->getNomcomplet()}.");
            return $this->redirectToRoute('app_employe_payer', ['id' => $employe->getId()]);
        }

        // Pré-calculer le nombre de jours réels du mois courant
        $nbJoursDefaut = (int) (new \DateTime())->format('t');

        return $this->render('employe/payer.html.twig', [
            'employe'       => $employe,
            'periodes'      => $periodes,
            'paiesEmploye'  => $paiesEmploye,
            'nbJoursDefaut' => $nbJoursDefaut,
        ]);
    }

    #[Route('/fiche-paie/{id}', name: 'app_employe_fiche_paie', methods: ['GET'])]
    public function fichePaie(PaieEmploye $paieEmploye): Response
    {
        return $this->render('employe/fiche_paie.html.twig', [
            'paieEmploye' => $paieEmploye,
            'employe'     => $paieEmploye->getEmploye(),
            'logoBase64'  => $this->getLogoBase64(),
        ]);
    }

    #[Route('/fiche-paie/{id}/pdf', name: 'app_employe_fiche_paie_pdf', methods: ['GET'])]
    public function fichePaiePdf(PaieEmploye $paieEmploye, FPdfGenerator $pdfGenerator): Response
    {
        $pdfContent = $pdfGenerator->generateFichePaiePdf($paieEmploye);

        $employe  = $paieEmploye->getEmploye();
        $filename = sprintf('fiche-paie-%s-%s.pdf',
            strtolower(str_replace(' ', '-', $employe->getNomcomplet())),
            $paieEmploye->getPaie()->getLabel()
        );

        return new Response($pdfContent, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    private function getLogoBase64(): string
    {
        $path = dirname(__DIR__, 2) . '/public/assets/images/afya.png';
        if (!file_exists($path)) {
            return '';
        }
        return 'data:image/png;base64,' . base64_encode(file_get_contents($path));
    }

    #[Route('/{id}', name: 'app_employe_delete', methods: ['POST'])]
    public function delete(Request $request, Employe $employe, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$employe->getId(), $request->request->get('_token'))) {
            $entityManager->remove($employe);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_employe_index', [], Response::HTTP_SEE_OTHER);
    }
}
