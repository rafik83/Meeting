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

class ActivateAccountMail extends Mail implements ParticipantMail
{
    /**
     * @var string
     */
    protected $subject = 'mail.activateAccount.subject';

    /**
     * @var string
     */
    protected $template = 'MailBundle:Mail:User/activateAccount.html.twig';

    /**
     * @var string
     */
    protected $messageId = Events::USER_ACCOUNT_ACTIVATED;

    /**
     * @var string
     */
    private $token;

    /**
     * @var bool
     */
    protected $sendToEmailTeam = false;

    /**
     * @param Event  $event
     * @param string $sender
     * @param string $receiver
     * @param string $locale
     * @param string $token
     * @param User   $senderUser
     * @param User   $receiverUser
     */
    public function __construct(
        Event $event,
        $sender,
        $receiver,
        $locale,
        $token,
        $senderUser,
        $receiverUser
    ) {
        parent::__construct($sender, $receiver, $locale, $senderUser, $receiverUser, $event);

        $this->token = $token;
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
     * {@inheritdoc}
     */
    public function getFirstname()
    {
        return $this->getReceiverUser()->getAccount()->getFirstName();
    }

    /**
     * {@inheritdoc}
     */
    public function getLastname()
    {
        return $this->getReceiverUser()->getAccount()->getLastName();
    }

    /**
     * {@inheritdoc}
     */
    public function getParticipantType()
    {
        return '';
    }
}
