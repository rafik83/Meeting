<?php

namespace Proximum\Vimeet\Application\View\Package;

class GroupsView extends AbstractProductsView
{
    /**
     * @var GroupView[]
     */
    public $groups = [];

    /**
     * @param GroupView[] $groups
     */
    public function __construct(array $groups)
    {
        $this->groups = $groups;
    }
}
