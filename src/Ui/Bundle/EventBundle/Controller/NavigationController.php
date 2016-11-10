<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller;

use Proximum\Vimeet\Application\Query\Navigation\MenuHeaderViewQuery;
use Proximum\Vimeet\Application\Query\Navigation\MenuViewQuery;
use Proximum\Vimeet\Application\Query\Navigation\SubmenuViewQuery;
use Proximum\Vimeet\Application\View\Navigation\MenuView;
use Proximum\Vimeet\Application\View\Navigation\SubmenuView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class NavigationController extends Controller
{
    /**
     * @param Request     $request
     * @param EventDomain $eventDomain
     *
     * @return Response
     */
    public function menuAction(Request $request, EventDomain $eventDomain)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        $menuView    = $this->mainMenu($eventDomain->getEvent(), $request->getLocale());
        $submenuView = $this->subMenu($eventDomain->getEvent(), $request->getLocale());

        return $this->render('EventBundle::Navigation/dropdownMenu.html.twig', [
            'menuView'    => $menuView,
            'submenuView' => $submenuView,
        ]);
    }

    /**
     * @param Request     $request
     * @param EventDomain $eventDomain
     *
     * @return Response
     */
    public function menuHeaderAction(Request $request, EventDomain $eventDomain)
    {
        $menuHeaderView = $this->get('tactician.commandbus.query')->handle(
            new MenuHeaderViewQuery($eventDomain->getEvent(), $request->getLocale(), $this->getUser())
        );

        return $this->render('EventBundle::Navigation/headerMenu.html.twig', [
            'menuHeader' => $menuHeaderView,
        ]);
    }

    /**
     * @param Event  $event
     * @param string $locale
     *
     * @return MenuView
     */
    private function mainMenu(Event $event, $locale)
    {
        $menuView = new MenuViewQuery($event, $this->getUser(), $locale);

        return $this->get('tactician.commandbus.query')->handle($menuView);
    }

    /**
     * @param Event  $event
     * @param string $locale
     *
     * @return SubmenuView
     */
    private function subMenu(Event $event, $locale)
    {
        $requestStack = $this->get('request_stack');
        $route        = $requestStack->getMasterRequest()->get('_route');

        $submenuView = $this->get('tactician.commandbus.query')->handle(
            new SubmenuViewQuery(
                $event,
                $this->getUser(),
                $locale,
                $route
            )
        );

        return $submenuView;
    }
}
