<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Meeting\Admin\ListMeeting;

use Proximum\Vimeet\Application\View\Meeting\Admin\ListMeeting\MeetingView;
use Proximum\Vimeet\Domain\Model\Participant;

class MeetingViewQueryHandler
{
    /** @var ParticipantViewQueryHandler */
    private $participantViewQueryHandler;

    public function __construct(ParticipantViewQueryHandler $participantViewQueryHandler)
    {
        $this->participantViewQueryHandler = $participantViewQueryHandler;
    }

    public function handle(MeetingViewQuery $query): MeetingView
    {
        $locale = $query->locale;

        return new MeetingView(
            $query->meeting->getId(),
            $query->meeting->getSpot()->getReference(),
            $query->meeting->getFromSheet()->getId(),
            $query->meeting->getFromSheet()->getTitle(),
            array_map(function (Participant $participant) use ($locale) {
                return $this->participantViewQueryHandler->handle(new ParticipantViewQuery($participant, $locale));
            }, $query->meeting->getFromParticipants()->toArray()),
            $query->meeting->getToSheet()->getId(),
            $query->meeting->getToSheet()->getTitle(),
            array_map(function (Participant $participant) use ($locale) {
                return $this->participantViewQueryHandler->handle(new ParticipantViewQuery($participant, $locale));
            }, $query->meeting->getToParticipants()->toArray())
        );
    }
}
