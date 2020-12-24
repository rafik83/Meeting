<?php

namespace Proximum\Vimeet\Application\Query\Meeting\Admin\Details;

use Proximum\Vimeet\Application\Components\Sheet\SheetInfoGuesser;
use Proximum\Vimeet\Application\View\Meeting\Admin\Details\MeetingView;
use Proximum\Vimeet\Application\View\Meeting\Admin\Details\ParticipantView;
use Proximum\Vimeet\Application\View\Meeting\Admin\Details\SheetView;
use Proximum\Vimeet\Application\View\Meeting\Admin\Details\SlotView;
use Proximum\Vimeet\Application\View\Meeting\Admin\Details\SpotView;
use Proximum\Vimeet\Domain\Template\ParticipantInfoGuesser;

class MeetingViewQueryHandler
{
    /** @var SheetInfoGuesser */
    private $sheetInfoGuesser;

    /** @var ParticipantInfoGuesser */
    private $participantInfoGuesser;

    public function __construct(
        SheetInfoGuesser $sheetInfoGuesser,
        ParticipantInfoGuesser $participantInfoGuesser
    ) {
        $this->sheetInfoGuesser       = $sheetInfoGuesser;
        $this->participantInfoGuesser = $participantInfoGuesser;
    }

    /**
     * @param MeetingViewQuery $meetingViewQuery
     *
     * @return MeetingView
     */
    public function handle(MeetingViewQuery $meetingViewQuery)
    {
        $meeting = $meetingViewQuery->meeting;
        $locale  = $meetingViewQuery->locale;

        $fromSheet = new SheetView(
            $meeting->getFromSheet()->getId(),
            $this->sheetInfoGuesser->guessSheetTitle($meeting->getFromSheet(), $locale)
        );

        $toSheet = new SheetView(
            $meeting->getToSheet()->getId(),
            $this->sheetInfoGuesser->guessSheetTitle($meeting->getToSheet(), $locale)
        );

        $fromParticipants = [];
        $toParticipants   = [];

        foreach ($meeting->getFromParticipants()->toArray() as $fromParticipant) {
            $fromParticipants[] = new ParticipantView(
                $this->participantInfoGuesser->guessParticipantCompleteName($fromParticipant, $locale),
                $this->participantInfoGuesser->guessParticipantMobile($fromParticipant, $locale)
            );
        }

        foreach ($meeting->getToParticipants()->toArray() as $toParticipant) {
            $toParticipants[] = new ParticipantView(
                $this->participantInfoGuesser->guessParticipantCompleteName($toParticipant, $locale),
                $this->participantInfoGuesser->guessParticipantMobile($toParticipant, $locale)
            );
        }

        $spot = new SpotView($meeting->getSpot()->getReference());
        $slot = new SlotView($meeting->getSlot()->getBegin(), $meeting->getSlot()->getEnd());

        return new MeetingView(
            $meeting->getId(),
            $meeting->getRequest()->getId(),
            $fromSheet,
            $fromParticipants,
            $toSheet,
            $toParticipants,
            $spot,
            $slot,
            $meeting->getCreatedAt()
        );
    }
}
