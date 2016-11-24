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
use Proximum\Vimeet\Application\Components\Mail\ParticipantMail;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\User;

class AddParticipantMail extends Mail implements ParticipantMail
{
    /**
     * @var string
     */
    protected $subject = 'mail.sheet.add_participant_confirmation.subject';

    /**
     * @var string
     */
    protected $template = 'MailBundle:Mail:Sheet/Invitation/addParticipantConfirmation.html.twig';

    /**
     * @var string
     */
    protected $messageId = Events::SHEET_ADD_PARTICIPANT_CONFIRMATION;

    /**
     * @var User
     */
    private $user;

    /**
     * @var Participant
     */
    private $guest;

    /**
     * @var bool
     */
    protected $sendToEmailTeam = false;

    /**
     * @var string
     */
    protected $firstname;

    /**
     * @var string
     */
    protected $lastname;

    /**
     * @param Event       $event
     * @param string      $sender
     * @param string      $receiver
     * @param string      $locale
     * @param User        $user
     * @param Participant $guest
     * @param string      $firstname
     * @param string      $lastname
     */
    public function __construct(
        Event $event,
        $sender,
        $receiver,
        $locale,
        User $user,
        Participant $guest,
        $firstname,
        $lastname
    ) {
        parent::__construct($sender, $receiver, $locale, null, null, $event);

        $this->user      = $user;
        $this->guest     = $guest;
        $this->firstname = $firstname;
        $this->lastname  = $lastname;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return Participant
     */
    public function getGuest()
    {
        return $this->guest;
    }

    /**
     * {@inheritdoc}
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * {@inheritdoc}
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * {@inheritdoc}
     */
    public function getParticipantType()
    {
        return $this->guest->getSheet()->getType()->getTitle($this->locale);
    }
}
