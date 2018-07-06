<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\User;

use Proximum\Vimeet\Application\Adapter\ElasticSearch\UserEventView\GetUserEventListViewsByEventInterface;
use Proximum\Vimeet\Domain\Model\PaginatedResult;

class GetUserEventListViewsQueryHandler
{
    /** @var GetUserEventListViewsByEventInterface */
    private $getUserEventViewsByEvent;

    public function __construct(GetUserEventListViewsByEventInterface $getUserEventViewsByEvent)
    {
        $this->getUserEventViewsByEvent = $getUserEventViewsByEvent;
    }

    public function handle(GetUserEventListViewsQuery $query): PaginatedResult
    {
        return $this->getUserEventViewsByEvent->handle($query->event, $query->page, $query->locale);
    }
}
