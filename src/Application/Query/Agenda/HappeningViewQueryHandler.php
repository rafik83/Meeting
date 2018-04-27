<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Agenda;

use Proximum\Vimeet\Application\View\Agenda\HappeningView;

class HappeningViewQueryHandler
{
    /**
     * @var SpeakerViewQueryHandler
     */
    private $speakerHandler;

    /**
     * @param SpeakerViewQueryHandler $speakerHandler
     */
    public function __construct(SpeakerViewQueryHandler $speakerHandler)
    {
        $this->speakerHandler = $speakerHandler;
    }

    /**
     * @param HappeningViewQuery $query
     *
     * @return HappeningView
     */
    public function handle(HappeningViewQuery $query)
    {
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
            $happening->getLimitParticipant()
        );
    }
}
