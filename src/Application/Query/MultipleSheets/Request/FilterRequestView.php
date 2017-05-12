<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) vimeet
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\MultipleSheets\Request;

use Proximum\Vimeet\Domain\Model\Sheet;

class FilterRequestView
{
    /** @var Sheet|null */
    public $otherSheet;

    /** @var string|null */
    public $type;

    /** @var string|null */
    public $state;
}
