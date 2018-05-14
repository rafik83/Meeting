<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Event\Happening;

use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\Participant;
use Symfony\Component\EventDispatcher\Event;

abstract class AbstractHappeningEvent extends Event
{
    /** @var Participant */
    public $participant;

    /** @var Happening */
    public $happening;

    /** @var bool */
    public $automaticallyThroughProductAttribution;

    public function __construct(
        Participant $participant,
        Happening $happening,
        bool $automaticallyThroughProductAttribution = false
    ) {
        $this->participant = $participant;
        $this->happening = $happening;
        $this->automaticallyThroughProductAttribution = $automaticallyThroughProductAttribution;
    }
}
