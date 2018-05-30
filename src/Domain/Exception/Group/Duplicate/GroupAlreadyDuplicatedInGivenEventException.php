<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Exception\Group\Duplicate;

use Proximum\Vimeet\Domain\Model\Sheet\Group;

class GroupAlreadyDuplicatedInGivenEventException extends DuplicateException
{
    /** @var Group */
    public $duplicatedGroup;

    public function __construct(Group $duplicatedGroup)
    {
        parent::__construct();

        $this->duplicatedGroup = $duplicatedGroup;
    }
}
