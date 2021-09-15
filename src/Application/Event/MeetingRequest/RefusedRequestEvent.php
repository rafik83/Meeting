<?php

namespace Proximum\Vimeet\Application\Event\MeetingRequest;

use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Model\User;
use Symfony\Component\EventDispatcher\Event;

class RefusedRequestEvent extends Event
{
    /**
     * @var User
     */
    private $emitter;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var \DateTimeInterface
     */
    private $date;

    /**
     * @var string
     */
    private $message;

    /**
     * RequestRefusedEvent constructor.
     *
     * @param User               $emitter
     * @param Request            $request
     * @param \DateTimeInterface $date
     * @param string             $message
     */
    public function __construct(User $emitter, Request $request, \DateTimeInterface $date, $message)
    {
        $this->emitter = $emitter;
        $this->request = $request;
        $this->date    = $date;
        $this->message = $message;
    }

    /**
     * Get emitter
     *
     * @return User
     */
    public function getEmitter()
    {
        return $this->emitter;
    }

    /**
     * Get request
     *
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Get date
     *
     * @return \DateTimeInterface
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Get message
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }
}
