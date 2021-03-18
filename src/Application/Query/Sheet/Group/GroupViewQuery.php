<?php

namespace Proximum\Vimeet\Application\Query\Sheet\Group;

use Proximum\Vimeet\Domain\Model\Sheet\Group;

class GroupViewQuery
{
    /** @var Group */
    public $group;

    /**
     * @param Group $group
     */
    public function __construct(Group $group)
    {
        $this->group = $group;
    }
}
