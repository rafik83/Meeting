<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Sheet;

use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;

class ChangeType
{
    /**
     * @var Sheet
     */
    public $sheet;

    /**
     * @var Type
     */
    public $type;

    /**
     * @param Sheet  $sheet
     * @param Type   $type
     */
    public function __construct(Sheet $sheet, Type $type)
    {
        $this->sheet = $sheet;
        $this->type  = $type;
    }
}
