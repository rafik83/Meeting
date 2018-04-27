<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller;

use Proximum\Vimeet\Application\Query\Catalog\External\CatalogVisibilityRegistrationUrlQuery;
use Proximum\Vimeet\Application\Query\Navigation\HeaderViewQuery;
use Proximum\Vimeet\Application\Query\Navigation\MenuViewQuery;
use Proximum\Vimeet\Application\Query\Navigation\SubmenuViewQuery;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Route\Route;
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
        $event = $eventDomain->getEvent();
        $locale = $request->getLocale();

        $requestStack    = $this->get('request_stack');
        $route           = $requestStack->getMasterRequest()->get('_route');
        $routeParameters = $requestStack->getMasterRequest()->get('_route_params');

        if (null === $route) {
            $route = Route::EVENT;
        }

        $menuHeaderView = $this->get('tactician.commandbus.query')->handle(
            new HeaderViewQuery(
                $eventDomain->getEvent(),
                $request->getLocale(),
                $route,
                null === $routeParameters ? [] : $routeParameters,
                $registration,
                $sheet,
                $user
            )
        );

        $menuView    = null;
        $submenuView = null;

        if (null !== $user && false === $registration) {
            $menuView = $this->get('tactician.commandbus.query')->handle(
                new MenuViewQuery($event, $locale, $sheet, $user)
            );

            $submenuView = $this->get('tactician.commandbus.query')->handle(
                new SubmenuViewQuery($event, $locale, $route, $sheet, $user)
            );
        }

        if (Route::EXTERNAL_CATALOG === $route) {
            $registrationUrl = $this->get('tactician.commandbus.query')->handle(
                new CatalogVisibilityRegistrationUrlQuery($event)
            );
        }

        $isShowingRegisterButton = Route::EVENT !== $route && null === $user;

        return $this->render('EventBundle::Navigation/header.html.twig', [
            'menuHeaderView'          => $menuHeaderView,
            'menuView'                => $menuView,
            'submenuView'             => $submenuView,
            'isShowingRegisterButton' => $isShowingRegisterButton,
            'isHeaderDisplayed'       => Route::EVENT === $route || Route::LOGIN === $route,
            'registrationUrl'         => $registrationUrl ?? null,
        ]);
    }
}
