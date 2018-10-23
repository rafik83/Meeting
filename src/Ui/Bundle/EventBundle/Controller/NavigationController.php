<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller;

use Proximum\Vimeet\Application\Components\Navigation\Route;
use Proximum\Vimeet\Application\Query\Catalog\External\CatalogVisibilityRegistrationUrlQuery;
use Proximum\Vimeet\Application\Query\Navigation\HeaderViewQuery;
use Proximum\Vimeet\Application\Query\Navigation\MenuViewQuery;
use Proximum\Vimeet\Application\Query\Navigation\SubmenuViewQuery;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\StaticFormulation\Constant;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ValueResolver\UserDomain;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Proximum\Vimeet\Infrastructure\Repository\StaticFormulation\StaticFormulationRepository;

class NavigationController extends Controller
{
    /**
     * @param Request         $request
     * @param EventDomain     $eventDomain
     * @param UserDomain|null $userDomain
     * @param Sheet|null      $sheet
     * @param bool            $registration
     *
     * @return Response
     */
    public function menuAction(
        Request $request,
        EventDomain $eventDomain,
        UserDomain $userDomain = null,
        Sheet $sheet = null,
        $registration = false
    ) {
        $event = $eventDomain->getEvent();
        $locale = $request->getLocale();
        $user = $userDomain instanceof UserDomain ? $userDomain->getUser() : null;

        $masterRequest = $this->get('request_stack')->getMasterRequest();

        if (null === $masterRequest) {
            throw new AccessDeniedException('This controller must be used as embedded');
        }

        $route = $masterRequest->get('_route', Route::EVENT);
        $routeParameters = $masterRequest->get('_route_params');

        $menuHeaderView = $this->get('tactician.commandbus.query')->handle(
            new HeaderViewQuery(
                $eventDomain->getEvent(),
                $request->getLocale(),
                $route,
                $routeParameters ?? [],
                $registration,
                $sheet,
                $user
            )
        );

        $menuView    = null;
        $submenuView = null;

        if (null !== $user && false === $registration) {
            $staticFormulationsIndexedByCategories = [];

            if (null !== $sheet) {
                $staticFormulations = $this
                    ->get(StaticFormulationRepository::class)
                    ->findByTypeAndLocale(
                        $sheet->getType(),
                        $locale
                    )
                ;
                $staticFormulationsIndexedByCategories = [];

                foreach ($staticFormulations as $staticFormulation) {
                    $key = Constant::STATIC_FORMULATION_LIST[$staticFormulation->getKey()]['categoryKey'];
                    $staticFormulationsIndexedByCategories[$key] = $staticFormulation;
                }
            }


            $menuView = $this->get('tactician.commandbus.query')->handle(
                new MenuViewQuery(
                    $event,
                    $locale,
                    $sheet,
                    $user,
                    $staticFormulationsIndexedByCategories
                )
            );

            $submenuView = $this->get('tactician.commandbus.query')->handle(
                new SubmenuViewQuery(
                    $event,
                    $locale,
                    $route,
                    $sheet,
                    $user,
                    $staticFormulationsIndexedByCategories
                )
            );
        }

        if (Route::EXTERNAL_CATALOG === $route) {
            $registrationUrl = $this->get('tactician.commandbus.query')->handle(
                new CatalogVisibilityRegistrationUrlQuery($event)
            );
        }

        $isShowingRegisterButton = Route::EVENT !== $route && null === $user;

        return $this->render('EventBundle::Navigation/header.html.twig', [
            'menuHeaderView' => $menuHeaderView,
            'menuView' => $menuView,
            'submenuView' => $submenuView,
            'isShowingRegisterButton' => $isShowingRegisterButton,
            'isHeaderDisplayedOnMobile' => Route::isHeaderDisplayedOnMobile($route),
            'registrationUrl' => $registrationUrl ?? null,
        ]);
    }
}
