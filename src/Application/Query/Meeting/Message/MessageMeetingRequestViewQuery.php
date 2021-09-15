<?php

namespace Proximum\Vimeet\Application\Query\Meeting\Message;

use Proximum\Vimeet\Domain\Model\Meeting;

class MessageMeetingRequestViewQuery
{
    /**
     * @var Meeting\Request
     */
    public $meetingRequest;

    /**
     * @var Meeting\Message
     */
    public $message;

    /**
     * @var string
     */
    public $sheetName;

    /**
     * @param Meeting\Request $meetingRequest
     * @param Meeting\Message $message
     * @param string          $sheetName
     */
    public function __construct(Meeting\Request $meetingRequest, Meeting\Message $message, $sheetName)
    {
        $this->meetingRequest = $meetingRequest;
        $this->message        = $message;
        $this->sheetName      = $sheetName;
    }
}
