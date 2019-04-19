<?php
/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

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
}
