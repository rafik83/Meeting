<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Meeting;

use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;

class Update
{
    /**
     * @var Meeting
     */
    public $meeting;

    /**
     * @var Sheet
     */
    public $sheet;

    /**
     * @var Participant[]
     */
    public $participants;

    /**
     * Update constructor.
     *
     * @param Meeting $meeting
     * @param Sheet   $sheet
     */
    public function __construct(Meeting $meeting, Sheet $sheet)
    {
        $this->meeting      = $meeting;
        $this->sheet        = $sheet;
        $this->participants = $this->getParticipants($meeting, $sheet);
    }

    /**
     * @param Meeting $meeting
     * @param Sheet   $sheet
     *
     * @return array
     */
    private function getParticipants(Meeting $meeting, Sheet $sheet)
    {
        if ($meeting->getFrom() === $sheet) {
            return $meeting->getFromParticipants()->toArray();
        }

        if ($meeting->getTo() === $sheet) {
            return $meeting->getToParticipants()->toArray();
        }

        throw new \InvalidArgumentException('This sheet do not participate to this meeting.');
    }
}
