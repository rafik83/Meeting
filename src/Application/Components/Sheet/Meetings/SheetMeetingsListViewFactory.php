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
        trigger_deprecation('vimeet', '1.80.0', 'This method has been marked as deprecated because it was calling MeetingSlotRepositoryInterface::findAvailableSlotIdByParticipantsIds that is not defined');

        $sheets = $this->sheetRepository->getSheets($event, $locale);

        $sheetMeetingsListViews = array_map(function (Sheet $sheet) use ($locale) {
            return $this->createFromSheet(
                $sheet,
                $locale,
                $this->meetingRepository->countMeetingsFromSheet($sheet),
                $this->meetingRepository->countMeetingsToSheet($sheet),
                $this->requestRepository->countApprovedRequestSentBySheet($sheet),
                $this->requestRepository->countApprovedPropositionReceivedBySheet($sheet)
            );
        }, $sheets);

        return $sheetMeetingsListViews;
    }

    public function createFromSheet(
        Sheet $sheet,
        string $locale,
        int $meetingsRequestsNumber,
        int $meetingsPropositionsNumber,
        int $requestsNumber,
        int $propositionsNumber
    ): SheetMeetingsListView {
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
            $requestsPropositionsTransformation
        );
    }
}
