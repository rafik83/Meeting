<?php

namespace Proximum\Vimeet\Application\Query\Meeting;

use Proximum\Vimeet\Application\View\Meeting\MeetingDdayView;
use Proximum\Vimeet\Domain\Template\ParticipantInfoGuesser;

class MeetingDDayViewQueryHandler
{
    /**
     * @var ParticipantInfoGuesser
     */
    private $participantInfoGuesser;

    /**
     * @param ParticipantInfoGuesser $participantInfoGuesser
     */
    public function __construct(ParticipantInfoGuesser $participantInfoGuesser)
    {
        $this->participantInfoGuesser = $participantInfoGuesser;
    }

    /**
     * @param MeetingDDayViewQuery $query
     *
     * @return MeetingDdayView
     */
    public function handle(MeetingDDayViewQuery $query): MeetingDdayView
    {
        $participantsFullname = [];

        foreach ($query->meeting->getToParticipants() as $participant) {
            $participantsFullname[] = $this->participantInfoGuesser
                ->guessParticipantCompleteName($participant, $query->locale);
        }

        return new MeetingDdayView(
            $query->meeting->getSlot()->getBegin(),
            $query->meeting->getSpot()->getReference(),
            $query->meeting->getEvent()->getTimeZone(),
            $query->locale,
            $participantsFullname
        );
    }
}
