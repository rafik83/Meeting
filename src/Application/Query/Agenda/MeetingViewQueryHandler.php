<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Agenda;

use Proximum\Vimeet\Application\Components\Sheet\SheetInfoGuesser;
use Proximum\Vimeet\Application\Query\Agenda\Meeting\ParticipantViewQueryHandler;
use Proximum\Vimeet\Application\View\Agenda\MeetingView;

class MeetingViewQueryHandler
{
    /**
     * @var ParticipantViewQueryHandler
     */
    private $participantHandler;


    /**
     * @var SheetInfoGuesser
     */
    private $sheetInfoGuesser;

    /**
     * @param ParticipantViewQueryHandler $participantHandler
     * @param SheetInfoGuesser            $sheetInfoGuesser
     */
    public function __construct(
        ParticipantViewQueryHandler $participantHandler,
        SheetInfoGuesser $sheetInfoGuesser
    ) {
        $this->participantHandler = $participantHandler;
        $this->sheetInfoGuesser   = $sheetInfoGuesser;
    }

    /**
     * @param MeetingViewQuery $query
     *
     * @return MeetingView
     */
    public function handle(MeetingViewQuery $query)
    {
        $sheetMet = $query->meeting->getSheetMet($query->currentSheet);

        if ($sheetMet === $query->meeting->getFromSheet()) {
            foreach ($query->meeting->getFromParticipants() as $participant) {

            }
        } elseif ($sheetMet === $query->meeting->getToSheet()) {
            foreach ($query->meeting->getToParticipants() as $participant) {

            }
        }

        $meeting  = new MeetingView(
            $sheetMet->getId(),
            $this->sheetInfoGuesser->guessSheetTitle($sheetMet, $query->locale),
            $query->meeting->getSlot()->getBegin(),
            $query->meeting->getSlot()->getEnd(),
            $query->meeting->getSpot()->getReference(),
            $query->event->getTimeZone(),
            $query->event->getConfiguration()->getLeftColor(),
            $query->event->getConfiguration()->getRightColor(),
            []
        );

        return $meeting;
    }
}
