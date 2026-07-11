<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('IS_AUTHENTICATED_FULLY')]
class DocumentationController extends AbstractController
{
    #[Route('/documentation', name: 'app_documentation')]
    public function index(): Response
    {
        return $this->render('documentation/index.html.twig');
    }
}
