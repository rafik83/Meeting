<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\User;

use Proximum\Vimeet\Application\Components\Mail\Mail;
use Proximum\Vimeet\Domain\Model\Event;

class ResetPasswordConfirmMail extends Mail
{
    /**
     * @var Event
     */
    private $event;

    /**
     * @param string $sender
     * @param string $receiver
     * @param string $template
     * @param string $messageId
     * @param string $locale
     * @param Event  $event
     */
    public function __construct($sender, $receiver, $template, $messageId, $locale, Event $event)
    {
        parent::__construct($sender, $receiver, $template, $messageId, $locale);

        $this->event = $event;
    }

    /**
     * @return Event
     */
    public function getEvent()
    {
        return $this->event;
    }
}
