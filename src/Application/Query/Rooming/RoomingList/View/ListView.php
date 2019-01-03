<?php

namespace Proximum\Vimeet\Application\Query\Rooming\RoomingList\View;

class ListView
{
    /** @var ListDetailView[] */
    public $listDetailViews;

    public function __construct(array $listDetailViews)
    {
        $this->listDetailViews = $listDetailViews;
    }
}
