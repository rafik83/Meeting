<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Agenda\Admin;

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
     * @param int    $sheetId
     * @param string $locale
     */
    public function __construct($sheetId, $locale)
    {
        $this->sheetId = $sheetId;
        $this->locale  = $locale;
    }
}
