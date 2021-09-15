<?php

namespace Proximum\Vimeet\Application\Query\Agenda\Admin;

use InvalidArgumentException;
use Proximum\Vimeet\Application\Components\Sheet\SheetInfoGuesser;
use Proximum\Vimeet\Application\Exception\Meeting\MeetingRequestCanNotBeMeetingException;
use Proximum\Vimeet\Application\Exception\MeetingRequest\NoSlotAvailableException;
use Proximum\Vimeet\Application\Exception\MeetingRequest\NoSpotAvailableException;
use Proximum\Vimeet\Application\View\Agenda\Admin\ParticipantView;
use Proximum\Vimeet\Application\View\Agenda\Admin\RequestView;
use Proximum\Vimeet\Domain\Meeting\MeetingParticipants;
use Proximum\Vimeet\Domain\Meeting\VisioGuesser;
use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Template\ParticipantInfoGuesser;
use Proximum\Vimeet\Ui\Helper\HasMeetingWithLinkedSheets;

class RequestViewQueryHandler
{
    /** @var SheetInfoGuesser */
    private $sheetInfoGuesser;

    /** @var ParticipantInfoGuesser */
    private $participantInfoGuesser;

    /** @var RequestSlotViewQueryHandler */
    private $requestSlotViewQueryHandler;

    /** @var VisioGuesser */
    private $visioGuesser;

    /** @var MeetingParticipants */
    private $meetingParticipants;

    /** @var HasMeetingWithLinkedSheets */
    private $hasApprovedMeetingRequestWithLinkedSheets;

    public function __construct(
        SheetInfoGuesser $sheetInfoGuesser,
        ParticipantInfoGuesser $participantInfoGuesser,
        RequestSlotViewQueryHandler $requestSlotViewQueryHandler,
        VisioGuesser $visioGuesser,
        MeetingParticipants $meetingParticipants,
        HasMeetingWithLinkedSheets $hasMeetingWithLinkedSheets
    ) {
        $this->sheetInfoGuesser = $sheetInfoGuesser;
        $this->participantInfoGuesser = $participantInfoGuesser;
        $this->requestSlotViewQueryHandler = $requestSlotViewQueryHandler;
        $this->visioGuesser = $visioGuesser;
        $this->meetingParticipants = $meetingParticipants;
        $this->hasMeetingWithLinkedSheets = $hasMeetingWithLinkedSheets;
    }

    public function handle(RequestViewQuery $query): RequestView
    {
        $sheetMet = $query->request->getSheetMet($query->sheet);

        $isVisio = $this->visioGuesser->hasMeetingRequestParticipantVisio($query->request);
        $isTransformableIntoMeeting = $this->isTransformableIntoMeeting($query->request, $isVisio);

        return new RequestView(
            $query->request->getId(),
            $this->sheetInfoGuesser->guessSheetTitle($sheetMet, $query->locale),
            $sheetMet->getId(),
            $this->getParticipantViews($query->request, $query->sheet, $query->locale),
            $isTransformableIntoMeeting,
            $query->request->isOneOfSheetsNotAttend(),
            $this->hasNoPreferenceAndNotAlone($query->request, $query->sheet),
            $this->hasNoPreferenceAndNotAlone($query->request, $sheetMet)
        );
    }

    private function hasNoPreferenceAndNotAlone(Request $request, Sheet $sheet): bool
    {
        return $request->hasNoPreference($sheet) && !$sheet->hasOnlyOneParticipant()
            && !$sheet->getType()->areAllSheetParticipantsAssignedToMeeting();
    }

    private function isTransformableIntoMeeting(Request $request, bool $isVisio): bool
    {
        if (!$request->isTransformableIntoMeeting()) {
            return false;
        }

        if ($this->hasMeetingWithLinkedSheets->isSatisfiedBy($request)) {
            return false;
        }

        try {
            $this->requestSlotViewQueryHandler->handle(
                new RequestSlotViewQuery($request, $isVisio)
            );

            return true;
        } catch (NoSlotAvailableException | NoSpotAvailableException | MeetingRequestCanNotBeMeetingException $e) {
        }

        return false;
    }

    /**
     * @param Request $request
     * @param Sheet   $sheet
     * @param string  $locale
     *
     * @return ParticipantView[]
     */
    private function getParticipantViews(Request $request, Sheet $sheet, $locale): array
    {
        $participantViews = [];

        try {
            $participants = $this->meetingParticipants->getMeetingParticipants($request, $sheet);

            foreach ($participants as $participant) {
                $participantViews[] = new ParticipantView(
                    $participant->getId(),
                    $this->participantInfoGuesser->guessParticipantCompleteName($participant, $locale)
                );
            }

            return $participantViews;
        } catch (InvalidArgumentException $exception) {
            return $participantViews;
        }
    }
}
