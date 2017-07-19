<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Event;

use Proximum\Vimeet\Domain\View\EventListView;

class EventListsView
{
    /** @var EventListView[] */
    public $list;

    /**
     * @param EventListView[] $list
     */
    public function __construct(array $list = [])
    {
        $this->list = $list;
    }
}
