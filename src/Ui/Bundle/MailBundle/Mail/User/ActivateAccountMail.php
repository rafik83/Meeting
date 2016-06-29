<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\User;

use Proximum\Vimeet\Application\Components\Mail\Mail;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;

class ActivateAccountMail extends Mail
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
     * @var User
     */
    private $senderUser;

    /**
     * @param string $sender
     * @param string $senderUser
     * @param string $receiver
     * @param string $template
     * @param string $messageId
     * @param string $locale
     * @param Event  $event
     * @param string $token
     */
    public function __construct($sender, $senderUser, $receiver, $template, $messageId, $locale, Event $event, $token)
    {
        parent::__construct($sender, $receiver, $template, $messageId, $locale);

        $this->event      = $event;
        $this->token      = $token;
        $this->senderUser = $senderUser;
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
