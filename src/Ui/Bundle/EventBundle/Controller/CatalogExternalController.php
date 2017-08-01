<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller;

use Proximum\Vimeet\Application\Query\Catalog\KeywordViewQuery;
use Proximum\Vimeet\Application\Query\Catalog\LocalizationViewQuery;
use Proximum\Vimeet\Application\Query\Sheet\Catalog\PaginatedSheetExternalViewQuery;
use Proximum\Vimeet\Domain\Catalog\ExternalCatalog;
use Proximum\Vimeet\Domain\Catalog\SearchFields;
use Proximum\Vimeet\Domain\Exception\Catalog\CatalogVisibilityNotFoundException;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class CatalogExternalController
 *
 * Routes are being protected by security access checker
 *
 * @see CatalogAccessEventListener
 */
class CatalogExternalController extends Controller
{
    /**
     * @param Request     $request
     * @param EventDomain $eventDomain
     *
     * @return Response
     */
    public function indexAction(Request $request, EventDomain $eventDomain): Response
    {
        $event   = $eventDomain->getEvent();
        $locale  = $request->getLocale();
        $page    = $request->query->getInt('page', 1);
        $filters = [];

        try {
            $searchForm = $this->get('form_factory.search_facet_external_factory')
                ->create($event, $locale, $filters);
            $typeViews = $this->get('form_factory.search_facet_external_factory')
                ->getTypeViews($event, $locale);

            $filters[SearchFields::FILTER_TYPE] = $typeViews;
        } catch (CatalogVisibilityNotFoundException $exception) {
            throw new NotFoundHttpException();
        }

        if ($searchForm->handleRequest($request)->isSubmitted() && $searchForm->isValid()) {
            $filters = $searchForm->getData();

            // if type field is empty, set the default types
            if (empty($filters[SearchFields::FILTER_TYPE])) {
                $filters[SearchFields::FILTER_TYPE] = $typeViews;
            }
        }

        $filters = array_merge($filters, ExternalCatalog::DEFAULT_FILTERS);

        $paginatedResult = $this->get('tactician.commandbus.query')->handle(
            new PaginatedSheetExternalViewQuery(
                $event,
                $filters,
                $page,
                48,
                $request->getLocale()
            )
        );

        $searchForm = $this->get('form_factory.search_facet_external_factory')
            ->createFiltered($event, $locale, $filters, $paginatedResult->aggregations);

        $seeMoreButtonStatus = $paginatedResult->total > ($paginatedResult->limit * $paginatedResult->page);

        if ($request->isXmlHttpRequest()) {
            $template = 'EventBundle:Catalog:External/catalog.html.twig';

            if ($page > 1) {
                return new JsonResponse(
                    [
                        'html'          => $this->renderView('EventBundle:Catalog/External:list.html.twig', [
                            'paginatedResult' => $paginatedResult,
                            'page'            => $page,
                        ]),
                        'seeMoreButton' => $seeMoreButtonStatus,
                    ]
                );
            }
        } else {
            $template = 'EventBundle:Catalog:External/index.html.twig';
        }

        return $this->render($template, [
            'event'             => $event,
            'page'              => 1,
            'paginatedResult'   => $paginatedResult,
            'seeMoreButton'     => $seeMoreButtonStatus,
            'searchForm'        => $searchForm->createView(),
            'catalogOnlineDate' => $event->getConfiguration()->getCatalogOnlineDate()->format('d/m/Y'),
            'typeViews'         => $typeViews
        ]);
    }

    /**
     * Get localization asynchronously
     *
     * @param Request     $request
     * @param EventDomain $eventDomain
     *
     * @return JsonResponse
     */
    public function searchLocalizationAction(Request $request, EventDomain $eventDomain): JsonResponse
    {
        if (!$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException();
        }

        $localizationView = $this->get('tactician.commandbus.query')->handle(
            new LocalizationViewQuery(
                $eventDomain->getEvent(),
                $request->get('query'),
                ExternalCatalog::DEFAULT_FILTERS,
                $request->getLocale()
            )
        );

        return new JsonResponse($localizationView);
    }

    /**
     * @param Request     $request
     * @param EventDomain $eventDomain
     *
     * @return JsonResponse
     */
    public function searchKeywordsAction(Request $request, EventDomain $eventDomain): JsonResponse
    {
        if (!$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException();
        }

        $keywordView = $this->get('tactician.commandbus.query')->handle(
            new KeywordViewQuery(
                $eventDomain->getEvent(),
                $request->get('query'),
                ExternalCatalog::DEFAULT_FILTERS,
                $request->getLocale()
            )
        );

        return new JsonResponse($keywordView);
    }
}
