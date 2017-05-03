<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Sheet\Group\Admin;

use Proximum\Vimeet\Domain\Model\Sheet\Group;

class AdminGroupViewQuery
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
