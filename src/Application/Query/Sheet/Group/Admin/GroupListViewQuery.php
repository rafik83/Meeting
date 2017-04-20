<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Sheet\Group\Admin;

use Proximum\Vimeet\Domain\Model\Sheet\Group;

class GroupListViewQuery
{
    /** @var Group[] */
    public $groups;

    /**
     * GroupListViewQuery constructor.
     *
     * @param Group[] $groups
     */
    public function __construct(array $groups)
    {
        $this->groups = $groups;
    }


}
