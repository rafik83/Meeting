<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\View\Meeting;

class AdminShowDetailsView
{
    /**
     * @var int
     */
    public $meetingRequestId;

    /**
     * @var int
     */
    public $fromSheetId;

    /**
     * @var string
     */
    public $fromSheetName;

    /**
     * @var int
     */
    public $toSheetId;

    /**
     * @var string
     */
    public $toSheetName;

    /**
     * @var array
     */
    public $fromParticipantNames = [];

    /**
     * @var array
     */
    public $toParticipantsNames  = [];

    /**
     * @var array
     */
    public $messages = [];

    /**
     * @var string
     */
    public $state;

    /**
     * @var \DateTimeInterface
     */
    public $createdAt;

    /**
     * AdminShowDetailsView constructor.
     *
     * @param string             $meetingRequestId
     * @param int                $fromSheetId
     * @param string             $fromSheetName
     * @param int                $toSheetId
     * @param string             $toSheetName
     * @param array              $toParticipantNames
     * @param array              $fromParticipantNames
     * @param array              $messages
     * @param string             $state
     * @param \DateTimeInterface $createdAt
     */
    public function __construct(
        $meetingRequestId,
        $fromSheetId,
        $fromSheetName,
        $toSheetId,
        $toSheetName,
        array $toParticipantNames,
        array $fromParticipantNames,
        array $messages,
        $state,
        \DateTimeInterface $createdAt
    ) {
        $this->meetingRequestId     = $meetingRequestId;
        $this->fromSheetId          = $fromSheetId;
        $this->fromSheetName        = $fromSheetName;
        $this->toSheetId            = $toSheetId;
        $this->toSheetName          = $toSheetName;
        $this->fromParticipantNames = $fromParticipantNames;
        $this->toParticipantNames   = $toParticipantNames;
        $this->messages             = $messages;
        $this->state                = $state;
        $this->createdAt            = $createdAt;
    }
}
