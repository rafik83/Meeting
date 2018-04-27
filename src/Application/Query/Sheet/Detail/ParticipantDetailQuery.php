<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Sheet\Detail;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;

class ParticipantDetailQuery
{
    /** @var Event */
    public $event;

    /** @var Sheet */
    public $sheet;

    /** @var string */
    public $locale;

    /**
     * ParticipantDetailQuery constructor.
     *
     * @param Sheet  $sheet
     * @param string $locale
     */
    public function __construct(Sheet $sheet, string $locale)
    {
        $this->event  = $sheet->getEvent();
        $this->sheet  = $sheet;
        $this->locale = $locale;
    }
}
