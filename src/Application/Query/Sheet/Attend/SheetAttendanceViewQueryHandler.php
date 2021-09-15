<?php

namespace Proximum\Vimeet\Application\Query\Sheet\Attend;

use Proximum\Vimeet\Application\Components\Sheet\SheetInfoGuesser;
use Proximum\Vimeet\Application\View\Sheet\Attend\SheetAttendanceView;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;

class SheetAttendanceViewQueryHandler
{
    /** @var MeetingRepositoryInterface */
    private $meetingRepository;

    /** @var SheetInfoGuesser */
    private $sheetInfoGuesser;

    /**
     * @param MeetingRepositoryInterface $meetingRepository
     * @param SheetInfoGuesser           $sheetInfoGuesser
     */
    public function __construct(MeetingRepositoryInterface $meetingRepository, SheetInfoGuesser $sheetInfoGuesser)
    {
        $this->meetingRepository = $meetingRepository;
        $this->sheetInfoGuesser  = $sheetInfoGuesser;
    }

    /**
     * @param SheetAttendanceViewQuery $sheetAttendanceViewQuery
     *
     * @return SheetAttendanceView
     */
    public function handle(SheetAttendanceViewQuery $sheetAttendanceViewQuery)
    {
        return new SheetAttendanceView(
            $sheetAttendanceViewQuery->sheet->getId(),
            $this->sheetInfoGuesser->guessSheetTitle($sheetAttendanceViewQuery->sheet),
            $this->meetingRepository->countMeetingsToSheet($sheetAttendanceViewQuery->sheet)
        );
    }
}
