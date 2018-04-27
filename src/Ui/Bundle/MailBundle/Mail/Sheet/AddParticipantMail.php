<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Sheet;

use Proximum\Vimeet\Application\Components\Mail\UserMail;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\View\Participant\ParticipantInfoView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;

class AddParticipantMail extends UserMail
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
     * @var bool
     */
    protected $sendToEmailTeam = false;

    /**
     * @var User
     */
    private $guest;

    /**
     * @param Event               $event
     * @param string              $sender
     * @param string              $receiver
     * @param string              $locale
     * @param User                $guest
     * @param ParticipantInfoView $participantInfoView
     */
    public function __construct(
        Event $event,
        $sender,
        $receiver,
        $locale,
        User $guest,
        ParticipantInfoView $participantInfoView
    ) {
        parent::__construct($sender, $receiver, $locale, $event, $participantInfoView);

        $this->guest = $guest;
    }

    /**
     * @return User
     */
    public function getGuest()
    {
        return $this->guest;
    }
}
