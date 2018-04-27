<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Sheet\Group;

use Proximum\Vimeet\Domain\Model\Sheet\Group;

class Update
{
    /** @var Group */
    public $group;

    /** @var string */
    public $email;

    /** @var string */
    public $title;

    /**
     * Update constructor.
     *
     * @param Group $group
     */
    public function __construct(Group $group)
    {
        $this->group = $group;
        $this->email = $group->getManager()->getEmail();
        $this->title = $group->getTitle();
    }
}
