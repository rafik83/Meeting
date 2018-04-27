<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

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
