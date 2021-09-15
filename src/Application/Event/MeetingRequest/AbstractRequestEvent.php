<?php

namespace Proximum\Vimeet\Application\Event\MeetingRequest;

use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Symfony\Component\EventDispatcher\Event;

abstract class AbstractRequestEvent extends Event
{
    /** @var Request */
    private $request;

    /**
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }
}
