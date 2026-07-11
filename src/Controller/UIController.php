<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UIController extends AbstractController
{
    public function ui_components_tabs(): Response
    {
        return $this->render('ui-components/tabs.html.twig');
    }

    public function ui_components_accordions(): Response
    {
        return $this->render('ui-components/accordions.html.twig');
    }

    public function ui_components_modals(): Response
    {
        return $this->render('ui-components/modals.html.twig');
    }

    public function ui_components_cards(): Response
    {
        return $this->render('ui-components/cards.html.twig');
    }

    public function ui_components_carousel(): Response
    {
        return $this->render('ui-components/carousel.html.twig');
    }

    public function ui_components_countdown(): Response
    {
        return $this->render('ui-components/countdown.html.twig');
    }

    public function ui_components_counter(): Response
    {
        return $this->render('ui-components/counter.html.twig');
    }

    public function ui_components_sweet_alert(): Response
    {
        return $this->render('ui-components/sweetalert.html.twig');
    }

    public function ui_components_timeline(): Response
    {
        return $this->render('ui-components/timeline.html.twig');
    }

    public function ui_components_notifications(): Response
    {
        return $this->render('ui-components/notifications.html.twig');
    }

    public function ui_components_media_object(): Response
    {
        return $this->render('ui-components/media-object.html.twig');
    }

    public function ui_components_list_group(): Response
    {
        return $this->render('ui-components/list-group.html.twig');
    }

    public function ui_components_pricing(): Response
    {
        return $this->render('ui-components/pricing-table.html.twig');
    }

    public function ui_components_lightbox(): Response
    {
        return $this->render('ui-components/lightbox.html.twig');
    }

    public function elements_alerts(): Response
    {
        return $this->render('elements/alerts.html.twig');
    }

    public function elements_avatar(): Response
    {
        return $this->render('elements/avatar.html.twig');
    }

    public function elements_badges(): Response
    {
        return $this->render('elements/badges.html.twig');
    }

    public function elements_breadcrumbs(): Response
    {
        return $this->render('elements/breadcrumbs.html.twig');
    }

    public function elements_buttons(): Response
    {
        return $this->render('elements/buttons.html.twig');
    }

    public function elements_button_group(): Response
    {
        return $this->render('elements/buttons-group.html.twig');
    }

    public function elements_color_library(): Response
    {
        return $this->render('elements/color-library.html.twig');
    }

    public function elements_dropdown(): Response
    {
        return $this->render('elements/dropdown.html.twig');
    }

    public function elements_infobox(): Response
    {
        return $this->render('elements/infobox.html.twig');
    }

    public function elements_jumbotron(): Response
    {
        return $this->render('elements/jumbotron.html.twig');
    }

    public function elements_loader(): Response
    {
        return $this->render('elements/loader.html.twig');
    }

    public function elements_pagination(): Response
    {
        return $this->render('elements/pagination.html.twig');
    }

    public function elements_popovers(): Response
    {
        return $this->render('elements/popovers.html.twig');
    }

    public function elements_progress_bar(): Response
    {
        return $this->render('elements/progress-bar.html.twig');
    }

    public function elements_search(): Response
    {
        return $this->render('elements/search.html.twig');
    }

    public function elements_tooltips(): Response
    {
        return $this->render('elements/tooltips.html.twig');
    }

    public function elements_treeview(): Response
    {
        return $this->render('elements/treeview.html.twig');
    }

    public function elements_typography(): Response
    {
        return $this->render('elements/typography.html.twig');
    }

    public function charts(): Response
    {
        return $this->render('charts.html.twig');
    }

    public function widgets(): Response
    {
        return $this->render('widgets.html.twig');
    }

    public function font_icons(): Response
    {
        return $this->render('font-icons.html.twig');
    }

    public function drag_and_drop(): Response
    {
        return $this->render('dragndrop.html.twig');
    }
}
