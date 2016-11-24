<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\User;

use Proximum\Vimeet\Application\Components\Mail\Mail;
use Proximum\Vimeet\Application\Components\Mail\ParticipantMail;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Domain\Model\Participant;

class CompleteProfileMail extends Mail implements ParticipantMail
{
    /**
     * @var string
     */
    protected $subject = 'mail.completeProfile.subject';

    /**
     * @var string
     */
    protected $template = 'MailBundle:Mail:User/completeProfile.html.twig';

    /**
     * @var string
     */
    protected $messageId = Events::USER_PROFILE_COMPLETED;

    /**
     * @var Participant
     */
    private $participant;

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
     * @param Participant $participant
     * @param string      $sender
     * @param string      $receiver
     * @param string      $locale
     * @param string      $firstname
     * @param string      $lastname
     */
    public function __construct(
        Participant $participant,
        $sender,
        $receiver,
        $locale,
        $firstname,
        $lastname
    ) {
        parent::__construct($sender, $receiver, $locale, null, null, $participant->getSheet()->getEvent());

        $this->participant = $participant;
        $this->firstname   = $firstname;
        $this->lastname    = $lastname;
    }

    /**
     * @return Participant
     */
    public function getParticipant()
    {
        return $this->participant;
    }

    /**
     * {@inheritdoc}
     */
    public function getSubjectParameters()
    {
        return [
            '%event%' => $this->getEvent()->getTitle(),
        ];
    }

    /**
     * @return string
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * @return string
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * @return string
     */
    public function getParticipantType()
    {
        return $this->participant->getSheet()->getType()->getTitle($this->locale);
    }
}
