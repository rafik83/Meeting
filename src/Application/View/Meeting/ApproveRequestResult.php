<?php

namespace Proximum\Vimeet\Application\View\Meeting;

use Proximum\Vimeet\Domain\Model\Meeting\Request;

class ApproveRequestResult
{
    /**
     * @var MeetingDdayView
     */
    public $meetingView;

    /**
     * @var bool
     */
    public $hasError;

    /**
     * @var Request
     */
    public $request;

    public function __construct(?MeetingDdayView $meetingView, bool $hasError, Request $request)
    {
        $this->meetingView = $meetingView;
        $this->hasError    = $hasError;
        $this->request = $request;
    }
}
