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
use Proximum\Vimeet\Application\View\Agenda\Slot\AvailableSlotView;
use Proximum\Vimeet\Application\View\Participant\ParticipantsSheetIdsView;
use Proximum\Vimeet\Application\View\Sheet\SheetIdsView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\PaginatedResult;
use Proximum\Vimeet\Domain\Model\Sheet;

interface SheetSearchAdapterInterface
{
    const ES_FIELD_TYPE                  = 'type';
    const ES_FIELD_CATEGORIES            = 'categories.id';
    const ES_FIELD_ORGANIZATION_CATEGORY = 'organizationCategory';
    const ES_FIELD_IN_CATALOG            = 'inCatalog';
    const ES_FIELD_POSITION              = 'position';

    /** ElasticSearch keys */
    const ES_BUCKETS   = 'buckets';
    const ES_DOC_COUNT = 'doc_count';
    const ES_KEY       = 'key';

    const ES_PATH_POSITION   = 'participants';
    const ES_PATH_CATEGORIES = 'categories';

    /**
     * @param Event               $event
     * @param array               $filters
     * @param string|null         $orderBy
     * @param int                 $page
     * @param int                 $limit
     * @param string              $locale
     * @param bool                $getAggregations
     * @param array               $nomenclatureItems
     * @param AvailableSlotView[] $availableSlotIds
     * @param Sheet[]             $sheetsToExclude
     *
     * @return PaginatedResult
     */
    public function find(
        Event $event,
        array $filters,
        string $orderBy = null,
        int $page,
        int $limit,
        string $locale,
        bool $getAggregations,
        array $nomenclatureItems = [],
        array $availableSlotIds = [],
        array $sheetsToExclude = []
    ): PaginatedResult;

    /**
     * @param Event  $event
     * @param array  $filters
     * @param string $locale
     *
     * @return int[]
     */
    public function getSheetIds(Event $event, array $filters, string $locale): array;

    /**
     * @param Event  $event
     * @param array  $filters
     * @param string $locale
     *
     * @return SheetListView[]
     */
    public function getSheetListView(Event $event, array $filters, string $locale): array;

    /**
     * @param Event  $event
     * @param array  $filters
     * @param string $locale
     *
     * @return SheetIdsView
     */
    public function getSheetIdsView(Event $event, array $filters, string $locale): SheetIdsView;

    /**
     * @param Event  $event
     * @param array  $filters
     * @param string $locale
     *
     * @return ParticipantsSheetIdsView
     */
    public function getParticipantsSheetIdsView(Event $event, array $filters, string $locale): ParticipantsSheetIdsView;

    /**
     * @param Event  $event
     * @param string $filter
     * @param array  $defaultFilters
     * @param string $locale
     *
     * @return array
     */
    public function findLocalization(Event $event, string $filter, array $defaultFilters, string $locale): array;

    /**
     * @param Event  $event
     * @param string $filter
     * @param array  $defaultFilters
     * @param string $locale
     *
     * @return array
     */
    public function findKeyword(Event $event, string $filter, array $defaultFilters, string $locale): array;

    /**
     * @param Event  $event
     * @param string $locale
     * @param array  $filters
     * @param string $filterToRemove
     * @param array  $nomenclatureItems
     * @param array  $availableSlotIds
     * @param array  $sheetsToExclude
     *
     * @return array
     */
    public function getTypeAggregations(
        Event $event,
        string $locale,
        array $filters,
        string $filterToRemove,
        array $nomenclatureItems = [],
        array $availableSlotIds = [],
        array $sheetsToExclude = []
    ): array;

    /**
     * @param Event  $event
     * @param string $locale
     * @param array  $filters
     * @param string $filterToRemove
     * @param array  $nomenclatureItems
     * @param array  $availableSlotIds
     * @param array  $sheetsToExclude
     *
     * @return array
     */
    public function getCategoryAggregations(
        Event $event,
        string $locale,
        array $filters,
        string $filterToRemove,
        array $nomenclatureItems = [],
        array $availableSlotIds = [],
        array $sheetsToExclude = []
    ): array;

    /**
     * @param Event  $event
     * @param string $locale
     * @param array  $filters
     * @param string $filterToRemove
     *
     * @return array
     */
    public function getOrganizationCategoryAggregations(
        Event $event,
        string $locale,
        array $filters,
        string $filterToRemove
    ): array;

    /**
     * @param Event  $event
     * @param string $locale
     * @param array  $filters
     * @param string $filterToRemove
     *
     * @return array
     */
    public function getPositionAggregations(
        Event $event,
        string $locale,
        array $filters,
        string $filterToRemove
    ): array;
}
