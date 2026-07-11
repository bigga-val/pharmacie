<?php

namespace App\Controller;

use App\Form\AlerteConfigType;
use App\Repository\AlerteConfigRepository;
use App\Service\AlerteService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/alertes')]
class AlerteController extends AbstractController
{
    #[Route('/', name: 'app_alerte_index', methods: ['GET'])]
    public function index(AlerteService $alerteService): Response
    {
        return $this->render('alerte/index.html.twig', [
            'alertes' => $alerteService->getAlertes(),
        ]);
    }

    #[Route('/config', name: 'app_alerte_config', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function config(
        Request                $request,
        AlerteConfigRepository $alerteConfigRepository,
        EntityManagerInterface $em
    ): Response {
        $config = $alerteConfigRepository->getConfig();
        $form   = $this->createForm(AlerteConfigType::class, $config);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Configuration des alertes mise à jour.');
            return $this->redirectToRoute('app_alerte_config');
        }

        return $this->renderForm('alerte/config.html.twig', [
            'form'   => $form,
            'config' => $config,
        ]);
    }
}
