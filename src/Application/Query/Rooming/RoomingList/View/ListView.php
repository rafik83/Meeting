<?php

namespace Proximum\Vimeet\Application\Query\Rooming\RoomingList\View;

class ListView
{
    /** @var ListDetailView[] */
    public $listDetailViews;

    /** @var int */
    public $listCount;

    public function __construct(array $listDetailViews, int $listCount)
    {
        $this->listDetailViews = $listDetailViews;
        $this->listCount = $listCount;
    }
}
