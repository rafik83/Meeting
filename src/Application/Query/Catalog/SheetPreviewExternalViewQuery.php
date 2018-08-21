<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Catalog;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;

class SheetPreviewExternalViewQuery
{
    /** @var Sheet */
    public $sheet;

    /** @var string */
    public $locale;

    /** @var Event */
    public $event;

    /** @var bool */
    public $showCategory;

    /**
     * @param Sheet  $sheet
     * @param string $locale
     * @param Event  $event
     * @param bool   $showCategory
     */
    public function __construct(Sheet $sheet, string $locale, Event $event, bool $showCategory = false)
    {
        $this->sheet  = $sheet;
        $this->locale = $locale;
        $this->event  = $event;
        $this->showCategory = $showCategory;
    }
}
