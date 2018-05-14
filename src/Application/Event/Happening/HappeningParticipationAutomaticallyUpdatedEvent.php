<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Event\Happening;


use Proximum\Vimeet\Domain\View\Happening\HappeningParticipationView;
use Symfony\Component\EventDispatcher\Event;

class HappeningParticipationAutomaticallyUpdatedEvent extends Event
{
    /** @var HappeningParticipationView[] */
    public $happeningParticipationViews;

    public function __construct(array $happeningParticipationViews)
    {
        $this->happeningParticipationViews = $happeningParticipationViews;
    }
}
