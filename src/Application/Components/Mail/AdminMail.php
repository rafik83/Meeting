<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Mail;

use Proximum\Vimeet\Domain\Model\AbstractUser;
use Proximum\Vimeet\Domain\Model\Event;

class AdminMail extends AbstractMail
{
    /**
     * @var Event|null
     */
    protected $event;

    /**
     * @var User|null
     */
    private $senderUser;

    /**
     * @var User|null
     */
    private $receiverUser;

    /**
     * @param string             $sender
     * @param string             $receiver
     * @param string             $locale
     * @param AbstractUser|null  $senderUser
     * @param AbstractUser|null  $receiverUser
     * @param Event|null $event
     */
    public function __construct(
        $sender,
        $receiver,
        $locale,
        ?AbstractUser $senderUser = null,
        ?AbstractUser $receiverUser = null,
        Event $event = null
    ) {
        parent::__construct($sender, $receiver, $locale);

        $this->senderUser   = $senderUser;
        $this->receiverUser = $receiverUser;
        $this->event        = $event;
    }

    /**
     * @return null|User
     */
    public function getSenderUser()
    {
        return $this->senderUser;
    }

    /**
     * @return null|User
     */
    public function getReceiverUser()
    {
        return $this->receiverUser;
    }

    /**
     * @return null|Event
     */
    public function getEvent()
    {
        return $this->event;
    }
}
