<?php

namespace Proximum\Vimeet\Application\Query\Agenda\Admin\Request;

use Proximum\Vimeet\Application\Components\Sheet\SheetInfoGuesser;
use Proximum\Vimeet\Application\View\Agenda\Admin\Request\RequestParticipantView;
use Proximum\Vimeet\Application\View\Agenda\Admin\Request\RequestSheetView;
use Proximum\Vimeet\Domain\Meeting\MeetingParticipants;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Template\ParticipantInfoGuesser;

class RequestSheetViewQueryHandler
{
    /**
     * @var SheetInfoGuesser
     */
    private $sheetInfoGuesser;

    /**
     * @var ParticipantInfoGuesser
     */
    private $participantInfoGuesser;

    /** @var MeetingParticipants */
    private $meetingParticipants;

    /**
     * RequestSheetViewQueryHandler constructor.
     *
     * @param SheetInfoGuesser       $sheetInfoGuesser
     * @param ParticipantInfoGuesser $participantInfoGuesser
     * @param MeetingParticipants    $meetingParticipants
     */
    public function __construct(
        SheetInfoGuesser $sheetInfoGuesser,
        ParticipantInfoGuesser $participantInfoGuesser,
        MeetingParticipants $meetingParticipants
    ) {
        $this->sheetInfoGuesser       = $sheetInfoGuesser;
        $this->participantInfoGuesser = $participantInfoGuesser;
        $this->meetingParticipants = $meetingParticipants;
    }

    /**
     * @param RequestSheetViewQuery $query
     *
     * @return RequestSheetView
     */
    public function handle(RequestSheetViewQuery $query)
    {
        $participantViews = [];

        /** @var Participant $participant */
        foreach ($query->sheet->getParticipants() as $participant) {
            $participate = false;

            if (in_array($participant, $this->meetingParticipants->getAllMeetingParticipants($query->request))) {
                $participate = true;
            }

            $participantViews[] = new RequestParticipantView(
                $participant->getId(),
                $this->participantInfoGuesser->guessParticipantCompleteName($participant, $query->locale),
                $participate
            );
        }

        return new RequestSheetView(
            $this->sheetInfoGuesser->guessSheetTitle($query->sheet, $query->locale),
            $participantViews
        );
    }
}
