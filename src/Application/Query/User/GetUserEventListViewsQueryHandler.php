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
use Proximum\Vimeet\Domain\UserEventView\UserEventListView;

class GetUserEventListViewsQueryHandler
{
    /** @var GetUserEventListViewsByEventInterface */
    private $getUserEventViewsByEvent;

    public function __construct(GetUserEventListViewsByEventInterface $getUserEventViewsByEvent)
    {
        $this->getUserEventViewsByEvent = $getUserEventViewsByEvent;
    }

    /**
     * @return UserEventListView[]
     */
    public function handle(GetUserEventListViewsQuery $query): array
    {
        return $this->getUserEventViewsByEvent->handle($query->event, $query->page);
    }
}
