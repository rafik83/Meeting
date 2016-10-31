<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\MeetingRequest;

use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\User;

class UpdateRequestFrom
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
     * @var User
     */
    public $editor;

    /**
     * UpdateRequestFrom constructor.
     *
     * @param Request $meetingRequest
     * @param User    $editor
     */
    public function __construct(Request $meetingRequest, User $editor)
    {
        $this->meetingRequest = $meetingRequest;
        $this->description    = null;
        $this->editor         = $editor;
        $this->participants   = $meetingRequest->getFromParticipants()->toArray();
    }
}
