<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Sheet\Detail;

use Proximum\Vimeet\Domain\Model\Sheet;

class SheetMeetingIndicatorQuery
{
    /** @var Sheet */
    public $sheet;

    /**
     * SheetMeetingIndicatorQuery constructor.
     *
     * @param Sheet $sheet
     */
    public function __construct(Sheet $sheet)
    {
        $this->sheet = $sheet;
    }
}
