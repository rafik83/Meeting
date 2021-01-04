<?php

namespace Proximum\Vimeet\Application\Query\Sheet\Group\Admin;

use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Sheet\Group;

class AdminGroupViewQuery
{
    /** @var Group */
    public $group;

    /** @var Admin */
    public $admin;

    /**
     * @param Group $group
     * @param Admin $admin
     */
    public function __construct(Group $group, Admin $admin)
    {
        $this->group = $group;
        $this->admin = $admin;
    }
}
