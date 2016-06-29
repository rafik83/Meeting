<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Event;

use Proximum\Vimeet\Application\Components\Mail\Mail;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;

class PreRegisteredMail extends Mail
{
    /**
     * @var Event
     */
    private $event;

    /**
     * @var Participant
     */
    private $participant;

    /**
     * @var Sheet
     */
    private $sheet;

    /**
     * RegisterAccountMail constructor.
     *
     * @param string      $sender
     * @param string      $receiver
     * @param string      $template
     * @param string      $messageId
     * @param string      $locale
     * @param Event       $event
     * @param User        $receiverUser
     * @param Participant $participant
     * @param Sheet       $sheet
     */
    public function __construct(
        $sender,
        $receiver,
        $template,
        $messageId,
        $locale,
        Event $event,
        User $receiverUser,
        Participant $participant,
        Sheet $sheet
    ) {
        parent::__construct($sender, $receiver, $template, $messageId, $locale, null, $receiverUser);

        $this->event       = $event;
        $this->sheet       = $sheet;
        $this->participant = $participant;
    }

    /**
     * @return Event
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @return Participant
     */
    public function getParticipant()
    {
        return $this->participant;
    }

    /**
     * @return Sheet
     */
    public function getSheet()
    {
        return $this->sheet;
    }
}
