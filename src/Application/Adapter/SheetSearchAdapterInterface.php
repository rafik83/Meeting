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
    const ES_FIELD_CATEGORY              = 'categories.id';
    const ES_FIELD_ORGANIZATION_CATEGORY = 'organizationCategory';
    const ES_FIELD_IN_CATALOG            = 'inCatalog';
    const ES_FIELD_POSITION              = 'position';

    /** ElasticSearch keys */
    const ES_BUCKETS   = 'buckets';
    const ES_DOC_COUNT = 'doc_count';
    const ES_KEY       = 'key';

    const ES_PATH_POSITION = 'participants';
    const ES_PATH_CATEGORY = 'categories';

    public function find(
        Event $event,
        array $filters,
        string $orderBy = null,
        int $page,
        int $limit,
        string $locale,
        bool $getAggregations,
        array $nomenclatureItems = []
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

    public function getSheetIdsView(Event $event, array $filters, string $locale): SheetIdsView;

    public function getParticipantsSheetIdsView(Event $event, array $filters, string $locale): ParticipantsSheetIdsView;

    public function findLocalization(Event $event, string $filter, array $defaultFilters, string $locale): array;

    public function findKeyword(Event $event, string $filter, array $defaultFilters, string $locale): array;

    public function getTypeAggregations(Event $event, string $locale, array $filters, string $filterToRemove): array;

    public function getCategoryAggregations(Event $event, string $locale, array $filters, string $filterToRemove): array;

    public function getOrganizationCategoryAggregations(
        Event $event,
        string $locale,
        array $filters,
        string $filterToRemove
    ): array;

    public function getPositionAggregations(
        Event $event,
        string $locale,
        array $filters,
        string $filterToRemove
    ): array;
}
