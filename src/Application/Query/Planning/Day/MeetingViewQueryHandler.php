<?php

namespace Proximum\Vimeet\Application\Query\Planning\Day;

use Proximum\Vimeet\Application\Command\Planning\ParticipantInfoGuesserCache;
use Proximum\Vimeet\Application\View\Planning\Day\MeetingView;
use Proximum\Vimeet\Application\View\Planning\Day\ParticipantMetView;
use Proximum\Vimeet\Domain\Model\Participant;

class MeetingViewQueryHandler
{
    /** @var ParticipantInfoGuesserCache */
    public $participantInfoGuesser;

    public function __construct(
        ParticipantInfoGuesserCache $participantInfoGuesser
    ) {
        $this->participantInfoGuesser = $participantInfoGuesser;
    }

    /**
     * @param MeetingViewQuery $query
     *
     * @return MeetingView
     */
    public function handle(MeetingViewQuery $query): MeetingView
    {
        $userSheet = $query->meeting->getSheetOfUser($query->user);
        $sheetMet = $query->meeting->getSheetMet($userSheet);
        $participantsMet = [];

        $displayParticipantName = $query->event->getConfiguration()->displayParticipantNameOnPlanning();
        $displayParticipantPosition = $query->event->getConfiguration()->displayParticipantPositionOnPlanning();

        if (true === $displayParticipantName || true === $displayParticipantPosition) {
            $participantsMet = $query->meeting->getParticipants($sheetMet);
        }

        $locale = $query->defaultLocale;

        return new MeetingView(
            $query->meeting->getSlot()->getBegin(),
            $query->meeting->getSlot()->getEnd(),
            $query->meeting->getSpot()->getReference(),
            true === $displayParticipantName || true === $displayParticipantPosition,
            array_map(function (Participant $participant) use ($locale, $displayParticipantName, $displayParticipantPosition) {
                return new ParticipantMetView(
                    true === $displayParticipantName
                        ? $this->participantInfoGuesser->guessParticipantCompleteName($participant, $locale)
                        : null,
                    true === $displayParticipantPosition
                        ? $this->participantInfoGuesser->guessParticipantPosition($participant, $locale)
                        : null
                );
            }, $participantsMet),
            $userSheet,
            $sheetMet
        );
    }
}
