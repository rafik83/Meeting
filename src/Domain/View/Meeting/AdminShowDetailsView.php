<?php

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
    public $toParticipantNames = [];

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
     * @var \DateTimeInterface
     */
    public $stateUpdatedAt;

    /**
     * AdminShowDetailsView constructor.
     *
     * @param int                $meetingRequestId
     * @param int                $fromSheetId
     * @param string             $fromSheetName
     * @param int                $toSheetId
     * @param string             $toSheetName
     * @param array              $fromParticipantNames
     * @param array              $toParticipantNames
     * @param array              $messages
     * @param string             $state
     * @param \DateTimeInterface $createdAt
     * @param \DateTimeInterface $stateUpdatedAt
     */
    public function __construct(
        $meetingRequestId,
        $fromSheetId,
        $fromSheetName,
        $toSheetId,
        $toSheetName,
        array $fromParticipantNames,
        array $toParticipantNames,
        array $messages,
        $state,
        \DateTimeInterface $createdAt,
        \DateTimeInterface $stateUpdatedAt
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
        $this->stateUpdatedAt       = $stateUpdatedAt;
    }
}
