<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller;

use Proximum\Vimeet\Application\Query\Navigation\HeaderViewQuery;
use Proximum\Vimeet\Application\Query\Navigation\MenuViewQuery;
use Proximum\Vimeet\Application\Query\Navigation\SubmenuViewQuery;
use Proximum\Vimeet\Application\View\Navigation\MenuView;
use Proximum\Vimeet\Application\View\Navigation\SubmenuView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;

class NavigationController extends Controller
{
    /**
     * @param Request       $request
     * @param EventDomain   $eventDomain
     * @param UserInterface $user
     * @param Sheet|null    $sheet
     * @param bool          $registration
     *
     * @return Response
     */
    public function menuAction(
        Request $request,
        EventDomain $eventDomain,
        UserInterface $user = null,
        Sheet $sheet = null,
        $registration = false
    ) {
        $requestStack    = $this->get('request_stack');
        $route           = $requestStack->getMasterRequest()->get('_route');
        $routeParameters = $requestStack->getMasterRequest()->get('_route_params');

        $menuHeaderView = $this->get('tactician.commandbus.query')->handle(
            new HeaderViewQuery(
                $eventDomain->getEvent(),
                $sheet,
                $request->getLocale(),
                $user,
                $route,
                $routeParameters,
                $registration
            )
        );

        $menuView    = null;
        $submenuView = null;

        if (null !== $user && false === $registration) {
            $menuView    = $this->mainMenu($eventDomain->getEvent(), $request->getLocale());
            $submenuView = $this->subMenu($eventDomain->getEvent(), $request->getLocale());
        }

        return $this->render('EventBundle::Navigation/header.html.twig', [
            'menuHeader'  => $menuHeaderView,
            'menuView'    => $menuView,
            'submenuView' => $submenuView,
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
