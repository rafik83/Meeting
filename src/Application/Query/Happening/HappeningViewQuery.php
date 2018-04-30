<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Happening;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Happening;

class HappeningViewQuery
{
    /**
     * @var Happening
     */
    public $happening;

    /**
     * @var string
     */
    public $locale;

    /**
     * @var Event
     */
    public $event;

    /**
     * HappeningViewQuery constructor.
     *
     * @param Happening $happening
     * @param Event     $event
     * @param string    $locale
     */
    public function __construct(Happening $happening, Event $event, $locale)
    {
        $this->happening = $happening;
        $this->event     = $event;
        $this->locale    = $locale;
    }
}
