<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\User;

use Proximum\Vimeet\Application\Components\Mail\Mail;
use Proximum\Vimeet\Application\Components\Mail\ParticipantMail;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;

class ChangeOldMailAddressMail extends Mail implements ParticipantMail
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
     * @var User
     */
    protected $receiverUser;

    /**
     * @param Event  $event
     * @param string $sender
     * @param string $receiver
     * @param string $locale
     * @param string $newMail
     * @param User   $receiverUser
     */
    public function __construct(Event $event, $sender, $receiver, $locale, $newMail, User $receiverUser)
    {
        parent::__construct($sender, $receiver, $locale, null, null, $event);

        $this->newMail      = $newMail;
        $this->event        = $event;
        $this->receiverUser = $receiverUser;
    }

    /**
     * @return string
     */
    public function getNewMail()
    {
        return $this->newMail;
    }

    /**
     * @return string
     */
    public function getFirstname()
    {
        return $this->receiverUser->getAccount()->getFirstName();
    }

    /**
     * @return string
     */
    public function getLastname()
    {
        return $this->receiverUser->getAccount()->getLastName();
    }

    /**
     * @return string
     */
    public function getParticipantType()
    {
        return '';
    }
}
