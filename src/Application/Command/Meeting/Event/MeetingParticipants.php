<?php
/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Meeting\Event;

use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;

class MeetingParticipants
{
    /**@var Request */
    public $request;

    /** @var Sheet */
    public $sheet;

    public function __construct(Request $request, Sheet $sheet = null)
    {

        $this->request = $request;
        $this->sheet = $sheet;
    }

    /**
     * @param bool $isFrom
     *
     * @return Participant[]
     */
    public function getMeetingParticipants(bool $isFrom) :array
    {
        $sheetParticipants = $this->sheet->getParticipants()->toArray();

        if ($this->sheet->getType()->areAllSheetParticipantsAssignedToMeeting()){
            if($this->sheet->hasLinkedSheets()){
                $linkedParticipants = $this->sheet->getLinkedSheetsParticipants();
                return array_combine($sheetParticipants, $linkedParticipants);
            }
            return $sheetParticipants;
        }

        if($isFrom){
            return $this->request->getFromParticipantsArray();
        }

        return $this->request->getToParticipantsArray();
    }
}
