<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Catalog\Export;

use Proximum\Vimeet\Application\Query\Query;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;

class SheetsViewQuery implements Query
{
    /** @var Event */
    public $event;

    /** @var Sheet */
    public $sheet;

    /** @var array */
    public $filters;

    /** @var string */
    public $locale;

    /** @var array */
    public $availableSlotIds;

    /** @var array */
    public $sheetsToExclude;

    /**
     * @param Event  $event
     * @param Sheet  $sheet
     * @param array  $filters
     * @param string $locale
     * @param array  $availableSlotIds
     * @param array  $sheetsToExclude
     */
    public function __construct(
        Event $event,
        Sheet $sheet,
        array $filters,
        string $locale,
        array $availableSlotIds = [],
        array $sheetsToExclude = []
    ) {
        $this->event            = $event;
        $this->sheet            = $sheet;
        $this->filters          = $filters;
        $this->locale           = $locale;
        $this->availableSlotIds = $availableSlotIds;
        $this->sheetsToExclude  = $sheetsToExclude;
    }
}
