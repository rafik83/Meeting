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
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;

class ChangeNewMailAddressMail extends Mail
{
    /**
     * @var string
     */
    protected $subject = 'mail.changeMailNew.subject';

    /**
     * @var string
     */
    protected $template = 'MailBundle:Mail:ChangeMail/newMail.html.twig';

    /**
     * @var string
     */
    protected $messageId = 'change_mail_new';

    /**
     * @var string
     */
    private $token;

    /**
     * @var User
     */
    private $user;

    /**
     * @param Event  $event
     * @param string $sender
     * @param string $receiver
     * @param string $locale
     * @param string $token
     * @param User   $user
     */
    public function __construct(Event $event, $sender, $receiver, $locale, $token, User $user)
    {
        parent::__construct($event, $sender, $receiver, $locale);

        $this->token = $token;
        $this->user  = $user;
    }

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }
}
