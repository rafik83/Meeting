<?php


namespace Proximum\Vimeet\Domain\Meeting;

use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;

class MeetingParticipants
{
    /**
     * @param Request $request
     * @param Sheet   $sheet
     *
     * @return Participant[]
     */
    public function getMeetingParticipants(Request $request, Sheet $sheet): array
    {
        if (!$request->isSender($sheet) && !$request->isReceiver($sheet)) {
            throw new \InvalidArgumentException('Sheet not concerned by this meeting request');
        }

        if ($sheet->getType()->areAllSheetParticipantsAssignedToMeeting()) {
            if ($sheet->hasLinkedSheets()) {
                return $sheet->getLinkedSheetsParticipants();
            }

            return $sheet->getParticipantsArray();
        }

        return $request->getParticipants($sheet);
    }

    /**
     * @param Request $request
     *
     * @return Participant[]
     */
    public function getAllMeetingParticipants(Request $request): array
    {
        return array_merge(
            $this->getMeetingParticipants($request, $request->getFromSheet()),
            $this->getMeetingParticipants($request, $request->getToSheet())
        );
    }

    public function countAllMeetingParticipants(Request $request): int
    {
        return \count($this->getAllMeetingParticipants($request));
    }
}
