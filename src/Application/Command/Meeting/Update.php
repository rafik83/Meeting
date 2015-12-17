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
use Proximum\Vimeet\Domain\Model\User;

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
     * @var User
     */
    public $user;

    /**
     * @var \DateTimeInterface
     */
    public $date;

    /**
     * @var string
     */
    public $message;

    /**
     * Update constructor.
     *
     * @param Meeting            $meeting
     * @param Sheet              $sheet
     * @param User               $user
     * @param \DateTimeInterface $date
     */
    public function __construct(Meeting $meeting, Sheet $sheet, User $user, \DateTimeInterface $date)
    {
        $this->meeting      = $meeting;
        $this->sheet        = $sheet;
        $this->participants = $this->getParticipants($meeting, $sheet);
        $this->user         = $user;
        $this->date         = $date;
    }

    /**
     * @param Meeting $meeting
     * @param Sheet   $sheet
     *
     * @return array
     */
    private function getParticipants(Meeting $meeting, Sheet $sheet)
    {
        if ($meeting->getFromSheet() === $sheet) {
            return $meeting->getFromParticipants()->toArray();
        }

        if ($meeting->getToSheet() === $sheet) {
            return $meeting->getToParticipants()->toArray();
        }

        throw new \InvalidArgumentException('This sheet do not participate to this meeting.');
    }
}
