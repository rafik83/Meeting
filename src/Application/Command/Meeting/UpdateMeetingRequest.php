<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Meeting;

use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;

class UpdateMeetingRequest
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
     * @var Sheet
     */
    public $sheetEditor;

    /**
     * UpdateRequest constructor.
     *
     * @param Request $meetingRequest
     * @param Sheet   $sheetEditor
     * @param User    $editor
     */
    public function __construct(Request $meetingRequest, Sheet $sheetEditor, User $editor)
    {
        $this->meetingRequest = $meetingRequest;
        $this->sheetEditor    = $sheetEditor;
        $this->editor         = $editor;
        $this->description    = null;

        if ($meetingRequest->isSender($sheetEditor)) {
            $this->participants = $meetingRequest->getFromParticipants()->toArray();
        } else {
            $this->participants = $meetingRequest->getToParticipants()->toArray();
        }
    }
}
