<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Agenda;

use Proximum\Vimeet\Domain\Model\Event;

class AgendaSheetViewQuery
{
    /**
     * @var int
     */
    public $sheetId;

    /**
     * @var string
     */
    public $locale;

    /**
     * AgendaSheetViewQuery constructor.
     *
     * @param int $sheetId
     */
    public function __construct($sheetId, $locale)
    {
        $this->sheetId = $sheetId;
        $this->locale  = $locale;
    }
}
