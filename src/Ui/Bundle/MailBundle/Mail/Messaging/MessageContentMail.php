<?php

namespace Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Messaging;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Messaging\Message;

class MessageContentMail
{
    /**
     * @var Message
     */
    private $message;

    /**
     * @var Event
     */
    private $event;

    /**
     * @var string
     */
    private $locale;

    /**
     * @param Message $message
     * @param Event   $event
     * @param string  $locale
     */
    public function __construct(Message $message, Event $event, $locale)
    {
        $this->message = $message;
        $this->event   = $event;
        $this->locale  = $locale;
    }

    /**
     * @return Message
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @return Event
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }
}
