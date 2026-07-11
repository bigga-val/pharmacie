<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PagesController extends AbstractController
{
    public function pages_knowledge_base(): Response
    {
        return $this->render('pages/knowledge-base.html.twig');
    }

    public function pages_contact_us(): Response
    {
        return $this->render('pages/contact-us.html.twig');
    }

    public function pages_faq(): Response
    {
        return $this->render('pages/faq.html.twig');
    }

    public function pages_coming_soon(): Response
    {
        return $this->render('pages/coming-soon.html.twig');
    }

    public function pages_error404(): Response
    {
        return $this->render('pages/error404.html.twig');
    }

    public function pages_error500(): Response
    {
        return $this->render('pages/error500.html.twig');
    }

    public function pages_error503(): Response
    {
        return $this->render('pages/error503.html.twig');
    }

    public function pages_maintenence(): Response
    {
        return $this->render('pages/maintenence.html.twig');
    }


}
