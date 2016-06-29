<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Mail;

use Proximum\Vimeet\Domain\Model\User;

class Mail
{
    /**
     * @var string
     */
    private $sender;

    /**
     * @var string
     */
    private $receiver;

    /**
     * @var User
     */
    private $senderUser;

    /**
     * @var User
     */
    private $receiverUser;

    /**
     * @var string
     */
    private $template;

    /**
     * @var string
     */
    private $messageId;

    /**
     * @var string
     */
    private $locale;

    /**
     * @param string $sender
     * @param string $receiver
     * @param string $template
     * @param string $messageId
     * @param string $locale
     * @param User   $senderUser
     * @param User   $receiverUser
     */
    public function __construct($sender, $receiver, $template, $messageId, $locale, User $senderUser = null, User $receiverUser = null)
    {
        $this->sender       = $sender;
        $this->receiver     = $receiver;
        $this->template     = $template;
        $this->messageId    = $messageId;
        $this->locale       = $locale;
        $this->receiverUser = $receiverUser;
        $this->senderUser   = $senderUser;
    }

    /**
     * @return string
     */
    public function getSender()
    {
        return $this->sender;
    }

    /**
     * @return string
     */
    public function getReceiver()
    {
        return $this->receiver;
    }

    /**
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * @return string
     */
    public function getMessageId()
    {
        return $this->messageId;
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @return User
     */
    public function getSenderUser()
    {
        return $this->senderUser;
    }

    /**
     * @return User
     */
    public function getReceiverUser()
    {
        return $this->receiverUser;
    }
}
