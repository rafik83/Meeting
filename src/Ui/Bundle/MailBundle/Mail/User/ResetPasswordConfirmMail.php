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
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;

class ResetPasswordConfirmMail extends Mail
{
    /**
     * @var string
     */
    protected $subject = 'mail.confirmResetPassword.subject';

    /**
     * @var string
     */
    protected $template = 'MailBundle:Mail:User/resetPasswordConfirm.html.twig';

    /**
     * @var string
     */
    protected $messageId = Events::USER_RESET_PASSWORD_CONFIRMED;

    /**
     * @var User
     */
    private $user;

    /**
     * @var bool
     */
    protected $sendToEmailTeam = false;

    /**
     * @param Event  $event
     * @param string $sender
     * @param string $receiver
     * @param string $locale
     * @param User   $user
     */
    public function __construct(Event $event, $sender, $receiver, $locale, User $user)
    {
        parent::__construct($sender, $receiver, $locale, null, null, $event);

        $this->user  = $user;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
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
