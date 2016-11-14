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
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Domain\Model\Participant;

class CompleteProfileMail extends Mail
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
     * @param Participant $participant
     * @param string      $sender
     * @param string      $receiver
     * @param string      $locale
     */
    public function __construct(Participant $participant, $sender, $receiver, $locale)
    {
        parent::__construct($sender, $receiver, $locale, null, null, $participant->getSheet()->getEvent());

        $this->participant = $participant;
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
}
