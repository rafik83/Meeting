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
use Proximum\Vimeet\Domain\UserEventView\UserEventView;

interface GetUserEventViewsByEventInterface
{
    public const RESULTS_NUMBER_BY_PAGE = 100;

    /**
     * @return UserEventView[]
     */
    public function handle(Event $event, int $page): array;
}
