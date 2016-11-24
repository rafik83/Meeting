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
use Proximum\Vimeet\Application\Components\Mail\ParticipantMail;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;

class RegisterAccountMail extends Mail implements ParticipantMail
{
    /**
     * @var string
     */
    protected $subject = 'mail.register.subject';

    /**
     * @var string
     */
    protected $template = 'MailBundle:Mail:User/register.html.twig';

    /**
     * @var string
     */
    protected $messageId = Events::USER_REGISTERED;

    /**
     * @var bool
     */
    protected $sendToEmailTeam = true;

    /**
     * @param Event  $event
     * @param string $sender
     * @param string $receiver
     * @param string $locale
     * @param User   $receiverUser
     */
    public function __construct(
        Event $event,
        $sender,
        $receiver,
        $locale,
        User $receiverUser
    ) {
        parent::__construct($sender, $receiver, $locale, null, $receiverUser, $event);
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
        return $this->getReceiverUser()->getAccount()->getFirstName();
    }

    /**
     * @return string
     */
    public function getLastname()
    {
        return $this->getReceiverUser()->getAccount()->getLastName();
    }

    /**
     * @return string
     */
    public function getParticipantType()
    {
        return '';
    }
}
