<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TablesController extends AbstractController
{
    public function tables(): Response
    {
        return $this->render('tables.html.twig');
    }

    public function datatables_advanced(): Response
    {
        return $this->render('datatables/advanced.html.twig');
    }

    public function datatables_alt_pagination(): Response
    {
        return $this->render('datatables/alt-pagination.html.twig');
    }

    public function datatables_basic(): Response
    {
        return $this->render('datatables/basic.html.twig');
    }

    public function datatables_checkbox(): Response
    {
        return $this->render('datatables/checkbox.html.twig');
    }

    public function datatables_clone_header(): Response
    {
        return $this->render('datatables/clone-header.html.twig');
    }

    public function datatables_column_chooser(): Response
    {
        return $this->render('datatables/column-chooser.html.twig');
    }

    public function datatables_export(): Response
    {
        return $this->render('datatables/export.html.twig');
    }

    public function datatables_multi_column(): Response
    {
        return $this->render('datatables/multi-column.html.twig');
    }

    public function datatables_multiple_tables(): Response
    {
        return $this->render('datatables/multiple-tables.html.twig');
    }
    
    public function datatables_order_sorting(): Response
    {
        return $this->render('datatables/order-sorting.html.twig');
    }

    public function datatables_range_search(): Response
    {
        return $this->render('datatables/range-search.html.twig');
    }

    public function datatables_skin(): Response
    {
        return $this->render('datatables/skin.html.twig');
    }

    public function datatables_sticky_header(): Response
    {
        return $this->render('datatables/sticky-header.html.twig');
    }
}
