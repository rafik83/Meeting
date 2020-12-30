<?php

namespace Proximum\Vimeet\Application\Event\MeetingRequest;

use Proximum\Vimeet\Domain\Model\Meeting\Message;
use Proximum\Vimeet\Domain\Model\User;
use Symfony\Component\EventDispatcher\Event;

class MessageEvent extends Event
{
    /**
     * @var Message
     */
    private $message;

    /**
     * @var User
     */
    private $emitter;

    /**
     * MessageEvent constructor.
     *
     * @param Message $message
     * @param User    $emitter
     */
    public function __construct(Message $message, User $emitter)
    {
        $this->message = $message;
        $this->emitter = $emitter;
    }

    /**
     * Get message
     *
     * @return Message
     */
    public function getMessage()
    {
        return $this->message;
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
}
