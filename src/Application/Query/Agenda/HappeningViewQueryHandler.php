<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Agenda;

use Proximum\Vimeet\Application\Query\Happening\Webinar\CanAccessToWebinar;
use Proximum\Vimeet\Application\View\Agenda\HappeningView;

class HappeningViewQueryHandler
{
    /** @var CanAccessToWebinar */
    private $canAccessToWebinar;

    /** @var SpeakerViewQueryHandler */
    private $speakerHandler;

    public function __construct(CanAccessToWebinar $canAccessToWebinar, SpeakerViewQueryHandler $speakerHandler)
    {
        $this->canAccessToWebinar = $canAccessToWebinar;
        $this->speakerHandler = $speakerHandler;
    }

    public function handle(HappeningViewQuery $query): HappeningView
    {
        $user = $query->user;
        $happening = $query->happening;
        $speakers  = $this->speakerHandler->handle(
            new SpeakerViewQuery($happening, $query->locale)
        );

        return new HappeningView(
            $happening->getId(),
            $happening->getBegin(),
            $happening->getEnd(),
            $happening->getTitle($query->locale),
            $happening->getDescription($query->locale),
            $speakers,
            $happening->getCategory()->getPicto(),
            $happening->getCategory()->getLeftColor(),
            $happening->getCategory()->getRightColor(),
            $query->event->getTimeZone(),
            $happening->getLimitParticipant(),
            $happening->isWebinar(),
            $happening->isWebinar() && $this->canAccessToWebinar->isSatisfiableBy($happening, $user)
        );
    }
}
