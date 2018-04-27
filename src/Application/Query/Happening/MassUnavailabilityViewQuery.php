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
use Proximum\Vimeet\Domain\Model\Unavailability\Mass;

class MassUnavailabilityViewQuery
{
    /**
     * @var Mass
     */
    public $mass;

    /**
     * @var string
     */
    public $locale;

    /**
     * @var Event
     */
    public $event;

    /**
     * @param Mass   $mass
     * @param Event  $event
     * @param string $locale
     */
    public function __construct(Mass $mass, Event $event, $locale)
    {
        $this->mass   = $mass;
        $this->event  = $event;
        $this->locale = $locale;
    }
}
