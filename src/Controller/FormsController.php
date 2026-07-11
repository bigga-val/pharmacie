<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FormsController extends AbstractController
{
    public function forms_basic(): Response
    {
        return $this->render('forms/basic.html.twig');
    }

    public function forms_input_group(): Response
    {
        return $this->render('forms/input-group.html.twig');
    }

    public function forms_layouts(): Response
    {
        return $this->render('forms/layouts.html.twig');
    }

    public function forms_validation(): Response
    {
        return $this->render('forms/validation.html.twig');
    }

    public function forms_input_mask(): Response
    {
        return $this->render('forms/input-mask.html.twig');
    }

    public function forms_checkbox_radio(): Response
    {
        return $this->render('forms/checkbox-radio.html.twig');
    }

    public function forms_select2(): Response
    {
        return $this->render('forms/select2.html.twig');
    }

    public function forms_switches(): Response
    {
        return $this->render('forms/switches.html.twig');
    }


    public function forms_wizards(): Response
    {
        return $this->render('forms/wizards.html.twig');
    }

    public function forms_file_upload(): Response
    {
        return $this->render('forms/file-upload.html.twig');
    }

    public function forms_quill_editor(): Response
    {
        return $this->render('forms/quill-editor.html.twig');
    }
    
    public function forms_markdown_editor(): Response
    {
        return $this->render('forms/markdown-editor.html.twig');
    }

    public function forms_date_picker(): Response
    {
        return $this->render('forms/date-picker.html.twig');
    }

    public function forms_clipboard(): Response
    {
        return $this->render('forms/clipboard.html.twig');
    }

    public function forms_touchspin(): Response
    {
        return $this->render('forms/touchspin.html.twig');
    }

}
