<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Type;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Type;

class Remove implements Command
{
    /**
     * @var Type
     */
    public $type;

    /**
     * Remove constructor.
     *
     * @param Type $type
     */
    public function __construct(Type $type)
    {
        $this->type = $type;
    }
}
