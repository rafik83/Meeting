<?php

namespace Proximum\Vimeet\Domain\View\Meeting;

class ShowDetailsView
{
    /**
     * @var int
     */
    public $meetingRequestId;

    /**
     * @var int
     */
    public $toSheetId;

    /**
     * @var string
     */
    public $toSheetName;

    /**
     * @var int
     */
    public $fromSheetId;

    /**
     * @var string
     */
    public $fromSheetName;

    /**
     * @var array
     */
    public $participantNames = [];

    /**
     * @var array
     */
    public $messages = [];

    /**
     * @var string
     */
    public $state;

    /**
     * ShowDetailsView constructor.
     *
     * @param $meetingRequestId
     * @param $toSheetId
     * @param $toSheetName
     * @param $fromSheetId
     * @param $fromSheetName
     * @param $participantNames
     * @param $messages
     * @param $state
     */
    public function __construct(
        $meetingRequestId,
        $toSheetId,
        $toSheetName,
        $fromSheetId,
        $fromSheetName,
        $participantNames,
        $messages,
        $state
    ) {
        $this->meetingRequestId = $meetingRequestId;
        $this->toSheetId        = $toSheetId;
        $this->toSheetName      = $toSheetName;
        $this->fromSheetId      = $fromSheetId;
        $this->fromSheetName    = $fromSheetName;
        $this->participantNames = $participantNames;
        $this->messages         = $messages;
        $this->state            = $state;
    }
}
