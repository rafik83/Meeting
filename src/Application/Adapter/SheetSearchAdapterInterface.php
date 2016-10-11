<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Adapter;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\PaginatedResult;

interface SheetSearchAdapterInterface
{
    const ES_FIELD_TYPE                  = 'type';
    const ES_FIELD_ORGANIZATION_CATEGORY = 'organizationCategory';
    const ES_FIELD_IN_CATALOG            = 'inCatalog';
    const ES_FIELD_POSITION              = 'position';

    /**
     * @param Event       $event
     * @param array       $filters
     * @param null|string $orderBy
     * @param int         $page
     * @param int         $limit
     * @param string      $locale
     * @param bool        $getAggregations
     *
     * @return PaginatedResult
     */
    public function find(Event $event, array $filters, $orderBy, $page, $limit, $locale, $getAggregations);

    /**
     * @param Event  $event
     * @param array  $filters
     * @param string $filterToRemove
     *
     * @return array
     */
    public function getTypeAggregations(Event $event, array $filters, $filterToRemove);

    /**
     * @param Event  $event
     * @param array  $filters
     * @param string $filterToRemove
     *
     * @return array
     */
    public function getOrganizationCategoryAggregations(Event $event, array $filters, $filterToRemove);
}
