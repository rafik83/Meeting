<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Sheet\ChangeType;

use Proximum\Vimeet\Domain\Model\Sheet;

class CancelPackage
{
    /** @var Sheet */
    public $sheet;

    public function __construct(Sheet $sheet)
    {
        $this->sheet = $sheet;
    }
}
