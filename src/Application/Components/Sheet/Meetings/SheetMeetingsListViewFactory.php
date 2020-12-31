<?php

namespace Proximum\Vimeet\Application\Components\Sheet\Meetings;

use Proximum\Vimeet\Application\Components\Sheet\SheetInfoGuesser;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\MeetingSlotRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class SheetMeetingsListViewFactory
{
    /**
     * @var SheetRepositoryInterface
     */
    private $sheetRepository;

    /**
     * @var RequestRepositoryInterface
     */
    private $requestRepository;

    /**
     * @var MeetingRepositoryInterface
     */
    private $meetingRepository;

    /**
     * @var MeetingSlotRepositoryInterface
     */
    private $meetingSlotRepository;

    /**
     * @var SheetInfoGuesser
     */
    private $sheetInfoGuesser;

    /**
     * @param SheetRepositoryInterface       $sheetRepository
     * @param RequestRepositoryInterface     $requestRepository
     * @param MeetingRepositoryInterface     $meetingRepository
     * @param MeetingSlotRepositoryInterface $meetingSlotRepository
     * @param SheetInfoGuesser               $sheetInfoGuesser
     */
    public function __construct(
        SheetRepositoryInterface $sheetRepository,
        RequestRepositoryInterface $requestRepository,
        MeetingRepositoryInterface $meetingRepository,
        MeetingSlotRepositoryInterface $meetingSlotRepository,
        SheetInfoGuesser $sheetInfoGuesser
    ) {
        $this->sheetRepository       = $sheetRepository;
        $this->requestRepository     = $requestRepository;
        $this->meetingRepository     = $meetingRepository;
        $this->meetingSlotRepository = $meetingSlotRepository;
        $this->sheetInfoGuesser      = $sheetInfoGuesser;
    }

    /**
     * @param Event  $event
     * @param string $locale
     *
     * @return SheetMeetingsListView[]
     */
    public function findAll(Event $event, $locale)
    {
        $sheets = $this->sheetRepository->getSheets($event, $locale);

        $sheetMeetingsListViews = array_map(function (Sheet $sheet) use ($locale) {
            $participantIds = array_map(function (Participant $participant) {
                return $participant->getId();
            }, $sheet->getParticipants()->toArray());

            return $this->createFromSheet(
                $sheet,
                $locale,
                $this->meetingRepository->countMeetingsFromSheet($sheet),
                $this->meetingRepository->countMeetingsToSheet($sheet),
                $this->requestRepository->countApprovedRequestSentBySheet($sheet),
                $this->requestRepository->countApprovedPropositionReceivedBySheet($sheet),
                count($this->meetingSlotRepository->findAvailableSlotIdByParticipantsIds($participantIds, true))
            );
        }, $sheets);

        return $sheetMeetingsListViews;
    }

    /**
     * @param Sheet  $sheet
     * @param string $locale
     * @param int    $meetingsRequestsNumber
     * @param int    $meetingsPropositionsNumber
     * @param int    $requestsNumber
     * @param int    $propositionsNumber
     * @param int    $availableSlots
     *
     * @return SheetMeetingsListView
     */
    public function createFromSheet(
        Sheet $sheet,
        $locale,
        $meetingsRequestsNumber,
        $meetingsPropositionsNumber,
        $requestsNumber,
        $propositionsNumber,
        $availableSlots
    ) {
        $requestsTransformation = 0 === $meetingsRequestsNumber
            ? 0
            : 100 * $meetingsRequestsNumber / $requestsNumber;

        $propositionsTransformation = 0 === $meetingsPropositionsNumber
            ? 0
            : 100 * $meetingsPropositionsNumber / $propositionsNumber;

        $transformationTotal = 0 === $requestsNumber + $propositionsNumber
            ? 0
            : 100 * ($meetingsRequestsNumber + $meetingsPropositionsNumber) / ($requestsNumber + $propositionsNumber);

        $requestsPropositionsTransformation = 0 === $requestsNumber + $propositionsNumber
            ? 0
            : 100 * $meetingsRequestsNumber / ($requestsNumber + $propositionsNumber);

        $filling = 0 === $availableSlots
            ? 0
            : 100 * ($meetingsRequestsNumber + $meetingsPropositionsNumber) / $availableSlots;

        return new SheetMeetingsListView(
            $sheet->getId(),
            $this->sheetInfoGuesser->guessSheetTitle($sheet, $locale),
            $sheet->getType()->getTitle($locale),
            $meetingsRequestsNumber,
            $meetingsPropositionsNumber,
            $requestsNumber,
            $propositionsNumber,
            $requestsTransformation,
            $propositionsTransformation,
            $transformationTotal,
            $requestsPropositionsTransformation,
            $availableSlots,
            $filling
        );
    }
}
