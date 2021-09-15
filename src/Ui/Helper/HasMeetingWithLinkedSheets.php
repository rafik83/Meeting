<?php


namespace Proximum\Vimeet\Ui\Helper;

use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;

class HasMeetingWithLinkedSheets
{
    /** @var MeetingRepositoryInterface */
    private $meetingRepository;

    public function __construct(MeetingRepositoryInterface $meetingRepository)
    {
        $this->meetingRepository = $meetingRepository;
    }

    public function isSatisfiedBy(Request $request): bool
    {
        $toSheet = $request->getToSheet();
        $fromSheet = $request->getFromSheet();

        if ($toSheet->hasLinkedSheets() || $fromSheet->hasLinkedSheets()) {
            $toSheetLinkedSheets = $toSheet->hasLinkedSheets() ? $toSheet->getLinkedSheets()->getSheets() : [$toSheet];
            $fromSheetLinkedSheets = $fromSheet->hasLinkedSheets() ? $fromSheet->getLinkedSheets()->getSheets() : [$fromSheet];

            return $this->meetingRepository->hasAtLeastOneMeeting($toSheetLinkedSheets, $fromSheetLinkedSheets);
        }

        return false;
    }
}
