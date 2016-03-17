<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\AppBundle\EventListener\Admin;

use Proximum\Vimeet\Application\Adapter\MailerInterface;
use Proximum\Vimeet\Application\Event\Admin\ActivateAccountEvent;
use Proximum\Vimeet\Bundle\AppBundle\Mail\Admin\ActivateAccountMail;

class ActivateAccountEventListener
{
    /**
     * @var MailerInterface
     */
    private $mailer;

    /**
     * @var string
     */
    private $sender;

    /**
     * @param MailerInterface $mailer
     * @param string          $sender
     */
    public function __construct(MailerInterface $mailer, $sender)
    {
        $this->mailer = $mailer;
        $this->sender = $sender;
    }

    /**
     * @param ActivateAccountEvent $event
     */
    public function sendToken(ActivateAccountEvent $event)
    {
        $mail = new ActivateAccountMail(
            $this->sender,
            $event->getAdmin()->getEmail(),
            'VimeetAppBundle:Mail:Admin/activateAccount.html.twig',
            'admin_activate_account',
            $event->getLocale(),
            $event->getActivateAccountToken()->getToken()
        );

        $this->mailer->send($mail);
    }
}
