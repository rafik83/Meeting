<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\MeetingRequest;

use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\User;

class EditRequest
{
    /**
     * @var Request
     */
    public $meetingRequest;

    /**
     * @var Participant[]
     */
    public $fromParticipants;

    /**
     * @var string
     */
    public $description;

    /**
     * @var \DateTimeInterface
     */
    public $date;

    /**
     * @var User
     */
    public $editor;

    /**
     * EditRequest constructor.
     *
     * @param Request            $meetingRequest
     * @param string             $description
     * @param \DateTimeInterface $date
     * @param User               $editor
     */
    public function __construct(Request $meetingRequest, $description, \DateTimeInterface $date, User $editor)
    {
        $this->meetingRequest   = $meetingRequest;
        $this->fromParticipants = $meetingRequest->getFromParticipants()->toArray();
        $this->description      = $description;
        $this->date             = $date;
        $this->editor           = $editor;
    }
}
