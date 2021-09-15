<?php

namespace Proximum\Vimeet\Application\Command\Sheet\LinkedSheets;

use Proximum\Vimeet\Application\Exception\Sheet\SheetNotFoundException;
use Proximum\Vimeet\Domain\Model\Sheet\LinkedSheets;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Sheet\LinkedSheetsRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class CreateHandler
{
    /** @var LinkedSheetsRepositoryInterface */
    private $linkedSheetsRepository;

    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    /** @var \DateTimeInterface */
    private $dateTime;

    /** @var MeetingRepositoryInterface */
    private $meetingRepository;

    public function __construct(
        LinkedSheetsRepositoryInterface $linkedSheetsRepository,
        SheetRepositoryInterface $sheetRepository,
        \DateTimeInterface $dateTime,
        MeetingRepositoryInterface $meetingRepository
    ) {
        $this->linkedSheetsRepository = $linkedSheetsRepository;
        $this->sheetRepository = $sheetRepository;
        $this->dateTime = $dateTime;
        $this->meetingRepository = $meetingRepository;
    }

    /**
     * @param Create $command
     *
     * @throws AlreadyLinkedException
     * @throws HasScheduledMeetingException
     * @throws LinkedSheetsTypeUniquenessException
     * @throws NotEnoughSheetsException
     * @throws SheetNotFoundException
     */
    public function handle(Create $command)
    {
        $linkedSheets = new LinkedSheets(
            $command->event,
            $this->dateTime
        );

        $type = null;

        $count = 0;
        foreach ($command->sheetViews as $sheetView) {
            $sheet = $this->sheetRepository->getSheetById($sheetView->id);

            if ($sheet === null) {
                throw new SheetNotFoundException('Not found for id'.$sheetView->id);
            }

            if ($sheet->hasLinkedSheets()) {
                throw new AlreadyLinkedException();
            }

            if ($type === null) {
                $type = $sheet->getType();
            }

            if ($type !== $sheet->getType()) {
                throw new LinkedSheetsTypeUniquenessException();
            }

            if ($this->meetingRepository->hasScheduledMeeting($sheet)) {
                throw new HasScheduledMeetingException();
            }

            $count++;
            $sheet->setLinkedSheets($linkedSheets);
        }

        if ($count < 2) {
            throw new NotEnoughSheetsException();
        }

        $this->linkedSheetsRepository->add($linkedSheets);
    }
}
