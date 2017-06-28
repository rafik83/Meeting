<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Event\Sheet;

use Proximum\Vimeet\Domain\Model\Sheet\Group;
use Symfony\Component\EventDispatcher;

class AbstractGroupEvent extends EventDispatcher\Event
{
    /** @var Group */
    private $group;

    /**
     * SheetGroupCreatedEvent constructor.
     *
     * @param Group $group
     */
    public function __construct(Group $group)
    {
        $this->group = $group;
    }

    /**
     * @return Group
     */
    public function getGroup()
    {
        return $this->group;
    }
}
