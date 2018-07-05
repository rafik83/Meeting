<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Adapter\ElasticSearch\UserEventView;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\UserEventView\UserEventListView;

interface GetUserEventListViewsByEventInterface
{
    public const RESULTS_NUMBER_BY_PAGE = 100;

    /**
     * @return UserEventListView[]
     */
    public function handle(Event $event, int $page, string $locale): array;
}
