<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller;

use Proximum\Vimeet\Application\Components\Rule\Strategy\SetNullStrategy;
use Proximum\Vimeet\Application\Exception\Paginator\UnavailableCurrentPageException;
use Proximum\Vimeet\Application\Query\Participant\CardListViewQuery;
use Proximum\Vimeet\Application\Query\Sheet\PaginatedCatalogSheetPreviewViewQuery;
use Proximum\Vimeet\Application\Query\Type\CatalogTypeViewQuery;
use Proximum\Vimeet\Domain\Exception\Catalog\SheetAccessDeniedException;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\View\CategoryView;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Catalog\SearchType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CatalogController extends Controller
{
    /**
     * @param Request     $request
     * @param EventDomain $eventDomain
     *
     * @return Response
     */
    public function indexAction(Request $request, EventDomain $eventDomain)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        $event = $eventDomain->getEvent();

        if (!$this->get('domain.key_dates.checker.catalog_access_checker')->allowedToAccess($event)) {
            throw $this->createNotFoundException();
        }

        $catalogTypeViewQuery = new CatalogTypeViewQuery($event, [], $request->getLocale());
        $typeViews            = $this->get('tactician.commandbus.query')->handle($catalogTypeViewQuery);
        $sheet = $this->get('sheet.sheet_guesser')->getUserSheet($this->getUser(), $event, $request->getLocale());

        if (!$sheet->isInCatalog()) {
            throw $this->createAccessDeniedException('Sheet not in catalog');
        }

        $visibleTypes = $this->getVisiblesTypes($event, $request->getLocale());
        $filters = ['orderBy' => Sheet\Constant::ORDER_BY_ALPHABETICAL];

        foreach ($typeViews as $typeId => $typeView) {
            if (array_key_exists($typeId, $visibleTypes)) {
                $filters['type'][] = $typeView;
            } else {
                if (count($visibleTypes) > 0) {
                    unset($typeViews[$typeId]);
                }
            }
        }

        $searchForm = $this->get('form.factory')->createNamed(
            '',
            SearchType::class,
            $filters,
            [
                'action'    => $this->generateUrl('event_catalog_index'),
                'typeViews' => $typeViews,
            ]
        );

        $filtered = $searchForm->handleRequest($request) && $searchForm->isValid();

        if ($filtered) {
            $filters = $searchForm->getData();
        }

        try {
            $paginatedCatalogSheetPreviewViewQuery = new PaginatedCatalogSheetPreviewViewQuery(
                $event,
                $filters,
                $request->query->getInt('page', 1),
                100,
                $request->getLocale()
            );
            $paginatedResult = $this->get('tactician.commandbus.query')->handle($paginatedCatalogSheetPreviewViewQuery);
        } catch (UnavailableCurrentPageException $exception) {
            throw $this->createNotFoundException($exception->getMessage());
        }

        $template = 'EventBundle:Catalog:index.html.twig';

        if ($request->isXmlHttpRequest()) {
            $template = 'EventBundle:Catalog:catalog.html.twig';
        }

        return $this->render($template, [
            'event'           => $event,
            'sheet'           => $sheet,
            'isCatalog'       => true,
            'typeViews'       => $typeViews,
            'paginatedResult' => $paginatedResult,
            'searchForm'      => $searchForm->createView(),
        ]);
    }

    /**
     * Display catalog categories of an event.
     *
     * @param Request     $request
     * @param EventDomain $eventDomain
     *
     * @return Response
     */
    public function categoriesAction(Request $request, EventDomain $eventDomain)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        if (!$this->get('domain.key_dates.checker.catalog_access_checker')->allowedToAccess($eventDomain->getEvent())) {
            throw $this->createNotFoundException();
        }

        $categories = $this
            ->get('vimeet_infrastructure.repository.category_repository')
            ->getCategoryViewsByEventAndUser($eventDomain->getEvent(), $this->getUser(), $request->getLocale());

        return $this->render('EventBundle:Catalog:categories.html.twig', [
            'event'      => $eventDomain->getEvent(),
            'categories' => $categories,
        ]);
    }

    /**
     * Display sheets matching category.
     *
     * @param EventDomain  $eventDomain
     * @param CategoryView $categoryView
     *
     * @return Response
     */
    public function categoryAction(EventDomain $eventDomain, CategoryView $categoryView)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        if (!$this->get('domain.key_dates.checker.catalog_access_checker')->allowedToAccess($eventDomain->getEvent())) {
            throw $this->createNotFoundException();
        }

        $sheets = $this
            ->get('vimeet_infrastructure.repository.sheet_repository')
            ->search($categoryView->id, $this->getUser());

        array_walk($sheets, function (Sheet &$sheet) {
            $rule = $this
                ->get('vimeet_infrastructure.application.components.rule.manager')
                ->getRule($sheet, $this->getUser());

            $this
                ->get('vimeet_infrastructure.application.components.rule.manager')
                ->apply($rule, $sheet, new SetNullStrategy());
        });

        return $this->render('EventBundle:Catalog:category.html.twig', [
            'event'        => $eventDomain->getEvent(),
            'categoryView' => $categoryView,
            'sheets'       => $sheets,
        ]);
    }

    /**
     * Display a sheet.
     *
     * @param Request     $request
     * @param EventDomain $eventDomain
     * @param Sheet       $sheet
     *
     * @return Response
     */
    public function sheetAction(Request $request, EventDomain $eventDomain, Sheet $sheet)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        $event = $eventDomain->getEvent();

        if (!$this->get('domain.key_dates.checker.catalog_access_checker')->allowedToAccess($event)) {
            throw $this->createNotFoundException();
        }

        if (!$sheet->isInCatalog()) {
            throw $this->createAccessDeniedException('Sheet not in catalog');
        }

        $locale = $request->getLocale();

        list ($nomenclatures, $participants, $taggedData) = $this->sheetInfos(
            $eventDomain->getEvent(),
            $sheet,
            $locale
        );
        $templateData = $this->get('template.template_data_factory')->createFromSheet($sheet, $locale);

        $userSheet = $this->get('sheet.sheet_guesser')->getUserSheet($this->getUser(), $event, $request->getLocale());

        return $this->render('EventBundle:Sheet:sheet.html.twig', [
            'event'         => $eventDomain->getEvent(),
            'sheet'         => $sheet,
            'taggedData'    => $taggedData,
            'locale'        => $locale,
            'nomenclatures' => $nomenclatures,
            'participants'  => $participants,
            'templateData'  => $templateData,
            'isCatalog'     => true,
            'userSheet'     => $userSheet,
        ]);
    }

    /**
     * @param Event $event
     * @param string $locale
     *
     * @return array
     * @throws \Exception
     */
    private function getVisiblesTypes(Event $event, $locale)
    {
        $sheet = $this->get('sheet.sheet_guesser')->getUserSheet($this->getUser(), $event, $locale);
        return $this->get('catalog.visible_participation_types')->getAllowedTypesList($sheet);
    }

    /**
     * @param Event  $event
     * @param Sheet  $sheet
     * @param string $locale
     *
     * @return array
     */
    private function sheetInfos(Event $event, Sheet $sheet, $locale)
    {
        $userSheet = $this->get('sheet.sheet_guesser')->getUserSheet($this->getUser(), $event, $locale);

        if (!$this->get('catalog.sheet_access_checker')->checkAccess($userSheet, $sheet)) {
            throw $this->createAccessDeniedException();
        }

        $nomenclatures     = $this->get('repository.nomenclature_repository')->findByEvent($event);
        $cardListViewQuery = new CardListViewQuery($sheet, $this->getUser(), $locale);
        $participants      = $this->get('tactician.commandbus.query')->handle($cardListViewQuery);

        $registrationTemplateData = $this
            ->get('template.template_data_factory')
            ->createRegistrationFromSheet($sheet, $locale);

        $taggedData = $registrationTemplateData->getAllTaggedDatas();

        return [$nomenclatures, $participants, $taggedData];
    }
}
