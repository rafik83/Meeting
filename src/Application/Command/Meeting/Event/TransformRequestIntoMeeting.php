<?php

namespace Proximum\Vimeet\Application\Command\Meeting\Event;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Meeting\Request;

class TransformRequestIntoMeeting implements Command
{
    /**
     * @var Request
     */
    public $request;

    /**
     * @var Event
     */
    public $event;

    /**
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->event   = $request->getEvent();
    }
}
