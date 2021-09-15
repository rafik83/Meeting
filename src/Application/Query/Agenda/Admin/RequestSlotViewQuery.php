<?php

namespace Proximum\Vimeet\Application\Query\Agenda\Admin;

use Proximum\Vimeet\Domain\Model\Meeting;

class RequestSlotViewQuery
{
    /** @var Meeting\Request */
    public $meetingRequest;

    /** @var bool */
    public $visio;

    /**
     * @param Meeting\Request $meetingRequest
     * @param bool            $visio
     */
    public function __construct(Meeting\Request $meetingRequest, $visio = false)
    {
        $this->meetingRequest = $meetingRequest;
        $this->visio          = $visio;
    }
}
