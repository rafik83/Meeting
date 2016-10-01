<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller;

use Proximum\Vimeet\Application\Exception\Paginator\UnavailableCurrentPageException;
use Proximum\Vimeet\Application\Query\Catalog\OrganizationCategoryViewQuery;
use Proximum\Vimeet\Application\Query\Catalog\TypeViewQuery;
use Proximum\Vimeet\Application\Query\Participant\CardListViewQuery;
use Proximum\Vimeet\Application\Query\Sheet\PaginatedCatalogSheetPreviewViewQuery;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\PaginatedResult;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\View\Catalog\OrganizationCategoryView;
use Proximum\Vimeet\Domain\View\Catalog\TypeView;
use Proximum\Vimeet\Infrastructure\Adapter\SheetSearchAdapter;
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

        $locale = $request->getLocale();

        $sheet = $this->get('sheet.sheet_guesser')->getUserSheet($this->getUser(), $event, $locale);

        if (!$sheet->isInCatalog()) {
            throw $this->createAccessDeniedException('Sheet not in catalog');
        }

        $visibleTypes = $this->get('catalog.visible_participation_types')->getAllowedTypesList($sheet);

        if (empty($visibleTypes)) {
            return $this->render('EventBundle:Catalog:no-visible-type.html.twig', ['event' => $event]);
        }

        $typeViews = $this->get('tactician.commandbus.query')->handle(
            new TypeViewQuery($event, $visibleTypes, $locale)
        );

        $organizationCategoryViews = $this->get('tactician.commandbus.query')->handle(
            new OrganizationCategoryViewQuery($event, $locale)
        );

        $filters = $this->getDefaultFilters($typeViews);

        $searchForm = $this->get('form.factory')->createNamed('', SearchType::class, $filters, [
            'action'                    => $this->generateUrl('event_catalog_index'),
            'typeViews'                 => $typeViews,
            'organizationCategoryViews' => $organizationCategoryViews,
        ]);

        if ($searchForm->handleRequest($request) && $searchForm->isValid()) {
            $filters = $searchForm->getData();
        }

        try {
            /** @var PaginatedResult $paginatedResult */
            $paginatedResult = $this->get('tactician.commandbus.query')->handle(
                new PaginatedCatalogSheetPreviewViewQuery(
                    $event,
                    $filters,
                    $request->query->getInt('page', 1),
                    100,
                    $locale,
                    $sheet
                )
            );
        } catch (UnavailableCurrentPageException $exception) {
            throw $this->createNotFoundException($exception->getMessage());
        }

        // @todo type aggs need to be done with a ES query without type filter
        $filteredTypeViews = $this->filterTypeViews(
            $typeViews,
            $paginatedResult->aggregations
        );

        // @todo organizationCategory aggs need to be done with a ES query without organizationCategory filter
        $filteredOrganizationCategoryViews = $this->filterOrganizationCategoryViews(
            $organizationCategoryViews,
            $paginatedResult->aggregations
        );

        $searchForm = $this->get('form.factory')->createNamed('', SearchType::class, $filters, [
            'action'                    => $this->generateUrl('event_catalog_index'),
            'typeViews'                 => $filteredTypeViews,
            'organizationCategoryViews' => $filteredOrganizationCategoryViews,
        ]);

        if ($request->isXmlHttpRequest()) {
            $template = 'EventBundle:Catalog:Partial/catalog.html.twig';
        } else {
            $template = 'EventBundle:Catalog:index.html.twig';
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
            throw $this->createAccessDeniedException();
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

        $rules = $this
            ->get('repository.rule_repository')
            ->getBySeerTypeAndSeeableType($userSheet->getType(), $sheet->getType())
        ;
        $ruleApplyer = $this->get('domain.rule.applyer');
        $ruleApplyer->applyRuleForTemplate($templateData, $rules);
        $ruleApplyer->applyRuleForCardList($participants, $rules);

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

    /**
     * @param TypeView[] $typeViews
     *
     * @return array
     */
    private function getDefaultFilters(array $typeViews)
    {
        $filters = ['orderBy' => Sheet\Constant::ORDER_BY_ALPHABETICAL];

        foreach ($typeViews as $typeView) {
            $filters['type'][] = $typeView;
        }

        return $filters;
    }

    /**
     * @param TypeView[] $typeViews
     * @param array|null $aggregations
     *
     * @return array
     */
    private function filterTypeViews(array $typeViews, array $aggregations = null)
    {
        $typeField = SheetSearchAdapter::ES_FIELD_TYPE;

        $aggregationsIndexedByKey = [];

        foreach ($aggregations[$typeField]['buckets'] as $item) {
            $aggregationsIndexedByKey[$item['key']] = $item['doc_count'];
        }

        foreach ($typeViews as $typeView) {
            if (isset($aggregationsIndexedByKey[$typeView->id])) {
                $typeView->count = $aggregationsIndexedByKey[$typeView->id];
            }
        }

        return $typeViews;
    }

    /**
     * @param OrganizationCategoryView[] $organizationCategoryViews
     * @param array|null                 $aggregations
     *
     * @return array
     */
    private function filterOrganizationCategoryViews(array $organizationCategoryViews, array $aggregations = null)
    {
        $organizationCategoryField = SheetSearchAdapter::ES_FIELD_ORGANIZATION_CATEGORY;

        if (null === $aggregations
            || !isset($aggregations[$organizationCategoryField])
            || !isset($aggregations[$organizationCategoryField]['buckets'])
            || count($aggregations[$organizationCategoryField]['buckets']) < 2
        ) {
            return [];
        }

        $aggregationsIndexedByKey = [];

        foreach ($aggregations[$organizationCategoryField]['buckets'] as $item) {
            $aggregationsIndexedByKey[$item['key']] = $item['doc_count'];
        }

        foreach ($organizationCategoryViews as $index => $organizationCategoryView) {
            if (!isset($aggregationsIndexedByKey[$organizationCategoryView->key]) || $aggregationsIndexedByKey[$organizationCategoryView->key] === 0) {
                unset($organizationCategoryViews[$index]);
            }
        }

        return $organizationCategoryViews;
    }
}
