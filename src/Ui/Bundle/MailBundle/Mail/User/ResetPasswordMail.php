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
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;

class ResetPasswordMail extends Mail implements ParticipantMail
{
    /**
     * @var string
     */
    protected $subject = 'mail.resetPassword.subject';

    /**
     * @var string
     */
    protected $template = 'MailBundle:Mail:User/resetPassword.html.twig';

    /**
     * @var string
     */
    protected $messageId = Events::USER_PASSWORD_RESET;

    /**
     * @var string
     */
    private $token;

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
     * @param string $token
     * @param User   $receiverUser
     */
    public function __construct(Event $event, $sender, $receiver, $locale, $token, User $receiverUser)
    {
        parent::__construct($sender, $receiver, $locale, null, null, $event);

        $this->token        = $token;
        $this->receiverUser = $receiverUser;
    }

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->token;
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
