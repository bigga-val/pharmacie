<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AppsController extends AbstractController
{
    public function chat(): Response
    {
        return $this->render('apps/chat.html.twig');
    }

    public function mailbox(): Response
    {
        return $this->render('apps/mailbox.html.twig');
    }

    public function todo_list(): Response
    {
        return $this->render('apps/todolist.html.twig');
    }

    public function notes(): Response
    {
        return $this->render('apps/notes.html.twig');
    }

    public function scrumboard(): Response
    {
        return $this->render('apps/scrumboard.html.twig');
    }

    public function contacts(): Response
    {
        return $this->render('apps/contacts.html.twig');
    }

    public function invoice_list(): Response
    {
        return $this->render('apps/invoice/list.html.twig');
    }

    public function invoice_add(): Response
    {
        return $this->render('apps/invoice/add.html.twig');
    }

    public function invoice_preview(): Response
    {
        return $this->render('apps/invoice/preview.html.twig');
    }

    public function invoice_edit(): Response
    {
        return $this->render('apps/invoice/edit.html.twig');
    }

    public function calendar(): Response
    {
        return $this->render('apps/calendar.html.twig');
    }
}
