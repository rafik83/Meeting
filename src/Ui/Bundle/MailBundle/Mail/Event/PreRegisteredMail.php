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
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;

class PreRegisteredMail extends Mail
{
    /**
     * @var string
     */
    protected $subject = 'mail.event.preregister.subject';

    /**
     * @var string
     */
    protected $template = 'MailBundle:Mail:Event/preregister.html.twig';

    /**
     * @var string
     */
    protected $messageId = Events::EVENT_PRE_REGISTERED;

    /**
     * @var Participant
     */
    private $participant;

    /**
     * @var Sheet
     */
    private $sheet;

    /**
     * @param Participant $participant
     * @param string      $sender
     * @param string      $receiver
     * @param string      $locale
     * @param User        $receiverUser
     */
    public function __construct(
        Participant $participant,
        $sender,
        $receiver,
        $locale,
        User $receiverUser
    ) {
        parent::__construct(
            $sender,
            $receiver,
            $locale,
            null,
            $participant->getUser(),
            $participant->getSheet()->getEvent()
        );

        $this->sheet       = $participant->getSheet();
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
     * @return Sheet
     */
    public function getSheet()
    {
        return $this->sheet;
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
