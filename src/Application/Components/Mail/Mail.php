<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Mail;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;

class Mail
{
    /**
     * @var Event|null
     */
    protected $event;

    /**
     * @var string
     */
    protected $subject;

    /**
     * @var array
     */
    protected $subjectParameters = [];

    /**
     * @var string
     */
    protected $template;

    /**
     * @var string
     */
    protected $messageId;

    /**
     * @var string
     */
    private $sender;

    /**
     * @var string
     */
    private $receiver;

    /**
     * @var User|null
     */
    private $senderUser;

    /**
     * @var User|null
     */
    private $receiverUser;

    /**
     * @var string
     */
    protected $locale;

    /**
     * @param string     $sender
     * @param string     $receiver
     * @param string     $locale
     * @param User|null  $senderUser
     * @param User|null  $receiverUser
     * @param Event|null $event
     */
    public function __construct(
        $sender,
        $receiver,
        $locale,
        User $senderUser = null,
        User $receiverUser = null,
        Event $event = null
    ) {
        $this->sender       = $sender;
        $this->receiver     = $receiver;
        $this->locale       = $locale;
        $this->senderUser   = $senderUser;
        $this->receiverUser = $receiverUser;
        $this->event        = $event;
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
     * @return User|null
     */
    public function getSenderUser()
    {
        return $this->senderUser;
    }

    /**
     * @return User|null
     */
    public function getReceiverUser()
    {
        return $this->receiverUser;
    }

    /**
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @return array
     */
    public function getSubjectParameters()
    {
        return $this->subjectParameters;
    }

    /**
     * @return Event|null
     */
    public function getEvent()
    {
        return $this->event;
    }
}
