<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller;

use Proximum\Vimeet\Application\Query\Navigation\MenuViewQuery;
use Proximum\Vimeet\Application\Query\Navigation\SubmenuViewQuery;
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

        $menuView = new MenuViewQuery($eventDomain->getEvent(), $this->getUser(), $request->getLocale());
        $menuView = $this->get('tactician.commandbus.query')->handle($menuView);

        return $this->render('EventBundle::Navigation/dropdownMenu.html.twig', [
            'menuView' => $menuView,
        ]);
    }

    /**
     * @param Request     $request
     * @param EventDomain $eventDomain
     *
     * @return Response
     */
    public function subMenuAction(Request $request, EventDomain $eventDomain)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        $requestStack = $this->get('request_stack');
        $route        = $requestStack->getMasterRequest()->get('_route');

        $submenuView = $this->get('tactician.commandbus.query')->handle(
            new SubmenuViewQuery(
                $eventDomain->getEvent(),
                $this->getUser(),
                $request->getLocale(),
                $route
            )
        );

        return $this->render('EventBundle::Navigation/submenu.html.twig', [
            'submenuView' => $submenuView,
        ]);
    }
}
