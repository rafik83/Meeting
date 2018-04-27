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
use Proximum\Vimeet\Application\View\Participant\ParticipantInfoView;
use Proximum\Vimeet\Domain\Model\Event;

class ChangeOldMailAddressMail extends UserMail
{
    /**
     * @var string
     */
    protected $subject = 'mail.changeMailOld.subject';

    /**
     * @var string
     */
    protected $template = 'MailBundle:Mail:ChangeMail/oldMail.html.twig';

    /**
     * @var string
     */
    protected $messageId = 'change_mail_old';

    /**
     * @var string
     */
    private $newMail;

    /**
     * @var bool
     */
    protected $sendToEmailTeam = false;

    /**
     * @param Event               $event
     * @param string              $sender
     * @param string              $receiver
     * @param string              $locale
     * @param string              $newMail
     * @param ParticipantInfoView $participantInfo
     */
    public function __construct(
        Event $event,
        $sender,
        $receiver,
        $locale,
        $newMail,
        ParticipantInfoView $participantInfo
    ) {
        parent::__construct($sender, $receiver, $locale, $event, $participantInfo);

        $this->newMail = $newMail;
    }

    /**
     * @return string
     */
    public function getNewMail()
    {
        return $this->newMail;
    }
}
