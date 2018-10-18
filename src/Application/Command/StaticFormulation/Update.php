<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\StaticFormulation;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\StaticFormulation;

class Update implements Command
{
    /** @var StaticFormulation */
    public $staticFormulation;

    public function __construct(StaticFormulation $staticFormulation)
    {
        $this->staticFormulation = $staticFormulation;
    }
}
