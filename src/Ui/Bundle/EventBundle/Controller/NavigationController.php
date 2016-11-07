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
    public function menuHeaderAction(Request $request, EventDomain $eventDomain)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        $menuHeaderView = $this->get('tactician.commandbus.query')->handle(
            new MenuHeaderViewQuery($eventDomain->getEvent(), $this->getUser(), $request->getLocale())
        );

        return $this->render('EventBundle::Navigation/headerMenu.html.twig', [
            'menuHeader' => $menuHeaderView,
        ]);
    }
}
