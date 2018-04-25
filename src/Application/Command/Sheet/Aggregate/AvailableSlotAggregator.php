<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Sheet\Aggregate;

use Proximum\Vimeet\Domain\Model\Sheet;

class AvailableSlotAggregator
{
    /** @var Sheet */
    public $sheet;

    /** @var bool */
    public $indexSheet;

    /**
     * @param Sheet $sheet
     * @param bool  $indexSheet
     */
    public function __construct(Sheet $sheet, bool $indexSheet = true)
    {
        $this->sheet = $sheet;
        $this->indexSheet = $indexSheet;
    }
}
