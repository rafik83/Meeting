<?php

namespace Proximum\Vimeet\Application\View\Invoice;

class GroupsView
{
    /**
     * @var GroupView[]
     */
    public $groupViews = [];

    /**
     * @param GroupView[] $groupViews
     */
    public function __construct(array $groupViews)
    {
        $this->groupViews = $groupViews;
    }
}
