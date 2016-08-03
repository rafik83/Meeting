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
     * @param string $sender
     * @param string $receiver
     * @param string $template
     * @param string $messageId
     * @param string $locale
     * @param Event  $event
     * @param string $token
     * @param User   $senderUser
     * @param User   $receiverUser
     */
    public function __construct(
        $sender,
        $receiver,
        $template,
        $messageId,
        $locale,
        Event $event,
        $token,
        $senderUser,
        $receiverUser
    ) {
        parent::__construct($sender, $receiver, $locale, $senderUser, $receiverUser);

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
