<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\AppBundle\Mail;

use Proximum\Vimeet\Application\Components\Mail\Mail;
use Proximum\Vimeet\Domain\Model\Event;

class ChangeMailAddressMail extends Mail
{
    /**
     * @var Event
     */
    private $event;

    /**
     * @var string
     */
    private $token;

    /**
     * @param string $sender
     * @param string $receiver
     * @param string $template
     * @param string $messageId
     * @param Event  $event
     * @param string $token
     */
    public function __construct($sender, $receiver, $template, $messageId, Event $event, $token)
    {
        parent::__construct($sender, $receiver, $template, $messageId);
        $this->event = $event;
        $this->token = $token;
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
    public function getToken()
    {
        return $this->token;
    }
}
