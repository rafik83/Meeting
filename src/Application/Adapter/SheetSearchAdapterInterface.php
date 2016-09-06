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
    /**
     * @param Event  $event
     * @param array  $filters
     * @param array  $orderBy
     * @param int    $page
     * @param int    $limit
     * @param string $locale
     *
     * @return PaginatedResult
     */
    public function find(Event $event, array $filters, array $orderBy, $page, $limit, $locale);

    /**
     * @param Event $event
     * @param array $filters
     *
     * @return array
     */
    public function getTypeStats(Event $event, array $filters);
}
