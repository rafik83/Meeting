<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\User;

use Proximum\Vimeet\Application\Components\Mail\UserMail;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\View\Participant\ParticipantInfoView;
use Proximum\Vimeet\Domain\Model\Participant;

class CompleteProfileMail extends UserMail
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
     * @param Participant         $participant
     * @param string              $sender
     * @param string              $receiver
     * @param string              $locale
     * @param ParticipantInfoView $participantInfoView
     */
    public function __construct(
        Participant $participant,
        $sender,
        $receiver,
        $locale,
        ParticipantInfoView $participantInfoView
    ) {
        parent::__construct(
            $sender,
            $receiver,
            $locale,
            $participant->getSheet()->getEvent(),
            $participantInfoView
        );

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
