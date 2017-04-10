<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Event;

use Proximum\Vimeet\Application\Query\Event\EventListQueryHandler;
use Proximum\Vimeet\Domain\View\EventListView;

/**
 * EventListsView contains a list of current events and a list of past events based on current date
 *
 * @see EventListQueryHandler::handle()
 */
class EventListsView
{
    /**
     * @var EventListView[]
     */
    public $currents;

    /**
     * @var EventListView[]
     */
    public $pasts;

    /**
     * EventListView constructor.
     *
     * @param EventListView[] $currentEvents
     * @param EventListView[] $pastEvents
     */
    public function __construct(array $currentEvents = [], array $pastEvents = [])
    {
        $this->currents = $currentEvents;
        $this->pasts    = $pastEvents;
    }
}
