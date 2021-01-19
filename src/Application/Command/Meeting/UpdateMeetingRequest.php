<?php

namespace Proximum\Vimeet\Application\Command\Meeting;

use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;

class UpdateMeetingRequest
{
    /** @var Request */
    public $meetingRequest;

    /** @var Participant[] */
    public $participants;

    /** @var string */
    public $description;

    /** @var Sheet */
    public $sheetEditor;

    /** @var bool */
    public $isPriority = false;

    /**
     * UpdateRequest constructor.
     *
     * @param Request $meetingRequest
     * @param Sheet   $sheetEditor
     */
    public function __construct(Request $meetingRequest, Sheet $sheetEditor)
    {
        $this->meetingRequest = $meetingRequest;
        $this->sheetEditor    = $sheetEditor;
        $this->description    = null;

        if ($meetingRequest->isSender($sheetEditor)) {
            $this->participants = $meetingRequest->getFromParticipants()->toArray();
        } else {
            $this->participants = $meetingRequest->getToParticipants()->toArray();
        }
    }
}
