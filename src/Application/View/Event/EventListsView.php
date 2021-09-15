<?php

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
