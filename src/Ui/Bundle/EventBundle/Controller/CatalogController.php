<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller;

use Proximum\Vimeet\Application\Adapter\SheetSearchAdapterInterface;
use Proximum\Vimeet\Application\Exception\Paginator\UnavailableCurrentPageException;
use Proximum\Vimeet\Application\Query\Catalog\OrganizationCategoryViewQuery;
use Proximum\Vimeet\Application\Query\Catalog\PositionViewQuery;
use Proximum\Vimeet\Application\Query\Catalog\TypeViewQuery;
use Proximum\Vimeet\Application\Query\Participant\CardListViewQuery;
use Proximum\Vimeet\Application\Query\Sheet\PaginatedCatalogSheetPreviewViewQuery;
use Proximum\Vimeet\Application\View\Catalog\PositionView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\PaginatedResult;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\View\Catalog\OrganizationCategoryView;
use Proximum\Vimeet\Domain\View\Catalog\TypeView;
use Proximum\Vimeet\Domain\View\CategoryView;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Catalog\SearchType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormInterface;
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

        $positionViews = $this->get('tactician.commandbus.query')->handle(
            new PositionViewQuery($event, $locale)
        );

        $filters = $this->getDefaultFilters($typeViews);

        $searchForm = $this->getSearchForm($filters, $typeViews, $organizationCategoryViews, $positionViews);

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

        $searchForm = $this->getFilteredSearchForm(
            $event,
            $locale,
            $visibleTypes,
            $filters,
            $paginatedResult->aggregations,
            $typeViews,
            $organizationCategoryViews,
            $positionViews
        );

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

        $userSheet = $this->get('sheet.sheet_guesser')->getUserSheet($this->getUser(), $event, $locale);

        $rules = $this
            ->get('repository.rule_repository')
            ->getBySeerTypeAndSeeableType($userSheet->getType(), $sheet->getType())
        ;

        if (empty($rules)) {
            throw $this->createNotFoundException('You do not have the right to see this sheet');
        }

        list ($nomenclatures, $participants, $taggedData) = $this->sheetInfos(
            $eventDomain->getEvent(),
            $sheet,
            $locale
        );
        $templateData = $this->get('template.template_data_factory')->createFromSheet($sheet, $locale);

        $ruleApplyer = $this->get('domain.rule.applyer');
        $ruleApplyer->applyRuleForTemplate($templateData, $rules);
        $ruleApplyer->applyRuleForCardList($participants, $rules);

        if ($sheet === $userSheet) {
            $meetingRequest = null;
        } else {
            $meetingRequest = $this
                ->get('vimeet_infrastructure.repository.meeting.request_repository')
                ->getRequestBetweenSheets($sheet, $userSheet);
        }

        return $this->render('EventBundle:Sheet:sheet.html.twig', [
            'event'          => $eventDomain->getEvent(),
            'sheet'          => $sheet,
            'taggedData'     => $taggedData,
            'locale'         => $locale,
            'nomenclatures'  => $nomenclatures,
            'participants'   => $participants,
            'templateData'   => $templateData,
            'isCatalog'      => true,
            'userSheet'      => $userSheet,
            'meetingRequest' => $meetingRequest,
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
        $filters = [
            SearchType::ORDER_BY    => Sheet\Constant::ORDER_BY_RELEVANCE,
            SearchType::FILTER_TYPE => $typeViews,
        ];

        return $filters;
    }

    /**
     * @param TypeView[] $typeViews
     * @param array|null $aggregations
     *
     * @return TypeView[]
     */
    private function filterTypeViews(array $typeViews, array $aggregations = null)
    {
        $typeField = SheetSearchAdapterInterface::ES_FIELD_TYPE;

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
     * @return OrganizationCategoryView[]
     */
    private function filterOrganizationCategoryViews(array $organizationCategoryViews, array $aggregations = null)
    {
        $organizationCategoryField = SheetSearchAdapterInterface::ES_FIELD_ORGANIZATION_CATEGORY;

        if (null === $aggregations
            || !isset($aggregations[$organizationCategoryField])
            || !isset($aggregations[$organizationCategoryField]['buckets'])
        ) {
            return [];
        }

        $aggregationsIndexedByKey = [];

        foreach ($aggregations[$organizationCategoryField]['buckets'] as $item) {
            $aggregationsIndexedByKey[$item['key']] = $item['doc_count'];
        }

        foreach ($organizationCategoryViews as $index => $organizationCategoryView) {
            // Show only filter which have result
            if (!isset($aggregationsIndexedByKey[$organizationCategoryView->key])
                || $aggregationsIndexedByKey[$organizationCategoryView->key] === 0
            ) {
                unset($organizationCategoryViews[$index]);
            }
        }

        return $organizationCategoryViews;
    }

    /**
     * @param PositionView[] $positionViews
     * @param array|null     $aggregations
     *
     * @return array
     */
    private function filterPositionViews(array $positionViews, array $aggregations = null)
    {
        $positionField = SheetSearchAdapterInterface::ES_FIELD_POSITION;

        if (null === $aggregations
            || !isset($aggregations[$positionField])
            || !isset($aggregations[$positionField][$positionField]['buckets'])
        ) {
            return [];
        }

        $aggregationsIndexedByKey = [];

        foreach ($aggregations[$positionField][$positionField]['buckets'] as $item) {
            $aggregationsIndexedByKey[$item['key']] = $item['doc_count'];
        }

        foreach ($positionViews as $index => $positionView) {
            // Show only filter which have result
            if (!isset($aggregationsIndexedByKey[$positionView->getKey()])
                || $aggregationsIndexedByKey[$positionView->getKey()] === 0
            ) {
                unset($positionViews[$index]);
            }
        }

        return $positionViews;
    }

    /**
     * @param array                      $filters
     * @param TypeView[]                 $typeViews
     * @param OrganizationCategoryView[] $organizationCategoryViews
     * @param PositionView[]             $positionViews
     *
     * @return FormInterface
     */
    private function getSearchForm(
        array $filters,
        array $typeViews,
        array $organizationCategoryViews,
        array $positionViews
    ) {
        return $this->get('form.factory')->createNamed('', SearchType::class, $filters, [
            'action'                    => $this->generateUrl('event_catalog_index'),
            'typeViews'                 => $typeViews,
            'organizationCategoryViews' => $organizationCategoryViews,
            'positionViews'             => $positionViews,
        ]);
    }

    /**
     * @param Event          $event
     * @param array          $visibleTypes
     * @param array          $filters
     * @param array          $currentAggregations
     * @param TypeView[]     $typeViews
     * @param CategoryView[] $organizationCategoryViews
     * @param PositionView[] $positionViews
     *
     * @return FormInterface
     */
    private function getFilteredSearchForm(
        Event $event,
        $locale,
        array $visibleTypes,
        array $filters,
        array $currentAggregations,
        array $typeViews,
        array $organizationCategoryViews,
        array $positionViews
    ) {
        $searchAdapter = $this->get('adapter.sheet_search_adapter');

        if (!isset($filters[SearchType::FILTER_TYPE])
            || count($filters[SearchType::FILTER_TYPE]) !== count($visibleTypes)
        ) {
            // if type filter is used, type aggs need to be done with a ES query without type filter
            $typeAggregations = $searchAdapter->getTypeAggregations(
                $event,
                $locale,
                $filters,
                SearchType::FILTER_TYPE
            );
        }

        if (isset($filters[SearchType::FILTER_ORGANIZATION_CATEGORY])) {
            // if organizationCategory filter is used,
            // organizationCategory aggs need to be done with a ES query without organizationCategory filter
            $categoryOrganisationAggregations = $searchAdapter->getOrganizationCategoryAggregations(
                $event,
                $locale,
                $filters,
                SearchType::FILTER_ORGANIZATION_CATEGORY
            );
        }

        if (isset($filters[SearchType::FILTER_POSITION])) {
            $positionAggregations = $searchAdapter->getPositionAggregations(
                $event,
                $locale,
                $filters,
                SearchType::FILTER_POSITION
            );
        }

        $filteredTypeViews = $this->filterTypeViews(
            $typeViews,
            isset($typeAggregations) ? $typeAggregations : $currentAggregations
        );

        $filteredOrganizationCategoryViews = $this->filterOrganizationCategoryViews(
            $organizationCategoryViews,
            isset($categoryOrganisationAggregations) ? $categoryOrganisationAggregations : $currentAggregations
        );

        $filteredPositionViews = $this->filterPositionViews(
            $positionViews,
            isset($positionAggregations) ? $positionAggregations : $currentAggregations
        );

        return $this->getSearchForm(
            $filters,
            $filteredTypeViews,
            $filteredOrganizationCategoryViews,
            $filteredPositionViews
        );
    }
}
