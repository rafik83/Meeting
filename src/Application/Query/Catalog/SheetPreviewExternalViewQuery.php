<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Catalog;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;

class SheetPreviewExternalViewQuery
{
    /**
     * @var Sheet
     */
    public $sheet;

    /**
     * @var string
     */
    public $locale;

    /**
     * @var Event
     */
    public $event;

    /**
     * SheetPreviewExternalViewQuery constructor.
     *
     * @param Sheet  $sheet
     * @param string $locale
     * @param Event  $event
     */
    public function __construct(Sheet $sheet, string $locale, Event $event)
    {
        $this->sheet  = $sheet;
        $this->locale = $locale;
        $this->event  = $event;
    }
}
