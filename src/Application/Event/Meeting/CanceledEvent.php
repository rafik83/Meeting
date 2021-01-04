<?php

namespace Proximum\Vimeet\Application\Event\Meeting;

use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\User;
use Symfony\Component\EventDispatcher\Event;

class CanceledEvent extends Event
{
    /**
     * @var User
     */
    private $emitter;

    /**
     * @var Meeting
     */
    private $meeting;

    /**
     * @var \DateTimeInterface
     */
    private $date;

    /**
     * @var string
     */
    private $message;

    /**
     * CanceledEvent constructor.
     *
     * @param User               $emitter
     * @param Meeting            $meeting
     * @param \DateTimeInterface $date
     * @param string             $message
     */
    public function __construct(User $emitter, Meeting $meeting, \DateTimeInterface $date, $message)
    {
        $this->emitter = $emitter;
        $this->meeting = $meeting;
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
     * Get meeting
     *
     * @return Meeting
     */
    public function getMeeting()
    {
        return $this->meeting;
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
