<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\MeetingRequest;

use Proximum\Vimeet\Application\Exception\MeetingRequest\IsNotAllowedToUpdateDataException;
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
     * EditRequest constructor.
     *
     * @param Request            $meetingRequest
     * @param \DateTimeInterface $date
     * @param User               $editor
     *
     * @throws IsNotAllowedToUpdateDataException
     */
    public function __construct(Request $meetingRequest, \DateTimeInterface $date, User $editor)
    {
        $this->meetingRequest = $meetingRequest;
        $this->description    = null;
        $this->date           = $date;
        $this->editor         = $editor;

        if ($meetingRequest->getUserSheet($editor) === $this->meetingRequest->getFromSheet()) {
            $this->participants = $this->meetingRequest->getFromParticipants()->toArray();
        } elseif ($meetingRequest->getUserSheet($editor) === $this->meetingRequest->getToSheet()) {
            $this->participants = $this->meetingRequest->getFromParticipants()->toArray();
        } else {
            throw new IsNotAllowedToUpdateDataException('You are not allowed to update this meeting request.');
        }
    }
}
