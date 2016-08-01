<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Sheet;

use Proximum\Vimeet\Application\Components\Mail\Mail;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;

class InvitationCloseToExpirationMail extends Mail
{
    /**
     * @var Event
     */
    private $event;

    /**
     * @var User
     */
    private $guest;

    /**
     * invitationCloseToExpirationMail constructor.
     *
     * @param string $sender
     * @param string $receiver
     * @param string $template
     * @param string $messageId
     * @param string $locale
     * @param Event  $event
     * @param User   $guest
     */
    public function __construct($sender, $receiver, $template, $messageId, $locale, Event $event, User $guest)
    {
        parent::__construct($sender, $receiver, $template, $messageId, $locale);

        $this->event = $event;
        $this->guest = $guest;
    }

    /**
     * @return Event
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @return User
     */
    public function getGuest()
    {
        return $this->guest;
    }
}
