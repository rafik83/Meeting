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
}
