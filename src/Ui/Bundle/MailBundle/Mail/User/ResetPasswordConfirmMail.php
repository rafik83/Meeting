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
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;

class ResetPasswordConfirmMail extends Mail
{
    /**
     * @var Event
     */
    private $event;

    /**
     * @var User
     */
    private $user;

    /**
     * @var null|Sheet
     */
    private $sheet;

    /**
     * @var null|Participant
     */
    private $participant;

    /**
     * @param string     $sender
     * @param string     $receiver
     * @param string     $template
     * @param string     $messageId
     * @param string     $locale
     * @param Event      $event
     * @param User       $user
     * @param null|Sheet $sheet
     */
    public function __construct(
        $sender,
        $receiver,
        $template,
        $messageId,
        $locale,
        Event $event,
        User $user,
        Sheet $sheet = null
    ) {
        parent::__construct($sender, $receiver, $template, $messageId, $locale);

        $this->event = $event;
        $this->sheet = $sheet;
        $this->user  = $user;

        if ($sheet !== null) {
            $participant = $sheet->getUserParticipant($user);

            if ($participant instanceof Participant) {
                $this->participant = $participant;
            }
        }
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
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return Sheet
     */
    public function getSheet()
    {
        return $this->sheet;
    }

    /**
     * @return Participant|null
     */
    public function getParticipant()
    {
        return $this->participant;
    }
}
