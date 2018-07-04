<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\User;

use Proximum\Vimeet\Application\Adapter\ElasticSearch\UserEventView\GetUserEventViewsByEventInterface;

class GetUserEventViewsQueryHandler
{
    /** @var GetUserEventViewsByEventInterface */
    private $getUserEventViewsByEvent;

    public function __construct(GetUserEventViewsByEventInterface $getUserEventViewsByEvent)
    {
        $this->getUserEventViewsByEvent = $getUserEventViewsByEvent;
    }

    public function handle(GetUserEventViewsQuery $query)
    {
        return $this->getUserEventViewsByEvent->handle($query->event, $query->page);
    }
}
