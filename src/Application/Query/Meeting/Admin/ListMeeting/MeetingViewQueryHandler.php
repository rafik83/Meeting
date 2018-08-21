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
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
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
        return new MeetingView(
            $query->meeting->getId(),
            $query->meeting->getSpot()->getReference(),
            $query->meeting->getFromSheet()->getId(),
            $query->meeting->getFromSheet()->getTitle(),
            $this->getParticipantViews(
                $query->meeting->getFromParticipants()->toArray(),
                $query->meeting->getEvent(),
                $query->meeting->getSlot(),
                $query->locale
            ),
            $query->meeting->getToSheet()->getId(),
            $query->meeting->getToSheet()->getTitle(),
            $this->getParticipantViews(
                $query->meeting->getToParticipants()->toArray(),
                $query->meeting->getEvent(),
                $query->meeting->getSlot(),
                $query->locale
            ),
            $query->meeting->getStatus()
        );
    }

    private function getParticipantViews(
        array $participants,
        Event $event,
        MeetingSlot $meetingSlot,
        string $locale
    ): array {
        return array_map(function (Participant $participant) use ($locale, $event, $meetingSlot) {
            return $this->participantViewQueryHandler->handle(
                new ParticipantViewQuery($participant, $event, $meetingSlot, $locale)
            );
        }, $participants);
    }
}
