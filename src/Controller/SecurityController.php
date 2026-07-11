<?php

namespace App\Controller;

use App\Repository\TauxRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    private entityManagerInterface $entityManager;
    #[Route(path: '/', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // if ($this->getUser()) {
        //     return $this->redirectToRoute('target_path');
        // }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    #[Route(path: '/setsession', name: 'app_set_sessions')]
    public function setSessions(Request $request,
                                TauxRepository $tauxRepository,
                                EntityManagerInterface $entityManager): Response
    {
        $tauxactif = $tauxRepository->findOneBy(['isActive' => true]);
        $request->getSession()->set('tauxactif', $tauxactif->getCout());
        return $this->redirectToRoute('app_dashboard', [], Response::HTTP_SEE_OTHER);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {

        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
