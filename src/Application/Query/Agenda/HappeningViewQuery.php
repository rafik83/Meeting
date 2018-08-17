<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Agenda;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Happening;

class HappeningViewQuery
{
    /** @var Happening */
    public $happening;

    /** @var Event */
    public $event;

    /** @var string */
    public $locale;

    public function __construct(Happening $happening, Event $event, $locale)
    {
        $this->happening = $happening;
        $this->event = $event;
        $this->locale = $locale;
    }
}
