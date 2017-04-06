<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Catalog;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;

class SheetPreviewViewQuery
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
     * @var Sheet
     */
    public $viewer;

    /**
     * @var Event
     */
    public $event;

    /**
     * @param Event  $event
     * @param Sheet  $sheet
     * @param string $locale
     * @param Sheet  $viewer
     */
    public function __construct(Event $event, Sheet $sheet, $locale, Sheet $viewer)
    {
        $this->event  = $event;
        $this->sheet  = $sheet;
        $this->locale = $locale;
        $this->viewer = $viewer;
    }
}
