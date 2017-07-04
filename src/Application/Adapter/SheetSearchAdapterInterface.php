<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Adapter;

use Proximum\Vimeet\Application\Query\Messaging\Campaign\SheetListView;
use Proximum\Vimeet\Application\View\Participant\ParticipantsSheetIdsView;
use Proximum\Vimeet\Application\View\Sheet\SheetIdsView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\PaginatedResult;

interface SheetSearchAdapterInterface
{
    const ES_FIELD_TYPE                  = 'type';
    const ES_FIELD_ORGANIZATION_CATEGORY = 'organizationCategory';
    const ES_FIELD_IN_CATALOG            = 'inCatalog';
    const ES_FIELD_POSITION              = 'position';

    /** ElasticSearch keys */
    const ES_BUCKETS = 'buckets';
    const ES_DOC_COUNT = 'doc_count';
    const ES_KEY = 'key';

    /**
     * @param Event       $event
     * @param array       $filters
     * @param null|string $orderBy
     * @param int         $page
     * @param int         $limit
     * @param string      $locale
     * @param bool        $getAggregations
     * @param array       $nomenclatureItems
     *
     * @return PaginatedResult
     */
    public function find(
        Event $event,
        array $filters,
        $orderBy,
        $page,
        $limit,
        $locale,
        $getAggregations,
        $nomenclatureItems = []
    ): PaginatedResult;

    /**
     * @param Event  $event
     * @param array  $filters
     * @param string $locale
     *
     * @return array
     */
    public function getSheetIds(Event $event, array $filters, $locale): array;

    /**
     * @param Event  $event
     * @param array  $filters
     * @param string $locale
     *
     * @return SheetListView[]
     */
    public function getSheetListView(Event $event, array $filters, $locale): array;

    /**
     * @param Event  $event
     * @param array  $filters
     * @param string $locale
     *
     * @return SheetIdsView
     */
    public function getSheetIdsView(Event $event, array $filters, $locale): SheetIdsView;

    /**
     * @param Event  $event
     * @param array  $filters
     * @param string $locale
     *
     * @return ParticipantsSheetIdsView
     */
    public function getParticipantsSheetIdsView(Event $event, array $filters, $locale): ParticipantsSheetIdsView;

    /**
     * @param Event  $event
     * @param string $filter
     * @param string $locale
     *
     * @return array
     */
    public function findLocalization(Event $event, $filter, $locale): array;

    /**
     * @param Event  $event
     * @param string $filter
     * @param string $locale
     *
     * @return array
     */
    public function findKeyword(Event $event, $filter, $locale): array;

    /**
     * @param Event  $event
     * @param string $locale
     * @param array  $filters
     * @param string $filterToRemove
     *
     * @return array
     */
    public function getTypeAggregations(Event $event, $locale, array $filters, $filterToRemove): array;

    /**
     * @param Event  $event
     * @param string $locale
     * @param array  $filters
     * @param string $filterToRemove
     *
     * @return array
     */
    public function getOrganizationCategoryAggregations(Event $event, $locale, array $filters, $filterToRemove): array;

    /**
     * @param Event  $event
     * @param string $locale
     * @param array  $filters
     * @param string $filterToRemove
     *
     * @return array
     */
    public function getPositionAggregations(Event $event, $locale, array $filters, $filterToRemove): array;
}
