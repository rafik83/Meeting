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

class SheetGroupUpdatedEvent extends EventDispatcher\Event
{
    /**
     * @var Group
     */
    private $group;

    /**
     * @var bool
     */
    private $isManagerChanged;

    /**
     * SheetGroupCreatedEvent constructor.
     *
     * @param Group $group
     * @param bool  $isManagerChanged
     */
    public function __construct(Group $group, $isManagerChanged)
    {
        $this->group            = $group;
        $this->isManagerChanged = $isManagerChanged;
    }

    /**
     * @return Group
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * @return bool
     */
    public function isManagerChanged()
    {
        return $this->isManagerChanged;
    }
}
