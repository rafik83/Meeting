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

class UpdateRequestTo
{
    /**
     * @var Request
     */
    public $meetingRequest;

    /**
     * @var Participant[]
     */
    public $participants;

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
     * UpdateRequestTo constructor.
     *
     * @param Request            $meetingRequest
     * @param \DateTimeInterface $date
     * @param User               $editor
     */
    public function __construct(Request $meetingRequest, \DateTimeInterface $date, User $editor)
    {
        $this->meetingRequest = $meetingRequest;
        $this->description    = null;
        $this->date           = $date;
        $this->editor         = $editor;
        $this->participants   = $meetingRequest->getToParticipants()->toArray();
    }
}
