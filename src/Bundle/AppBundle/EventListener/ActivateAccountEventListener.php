<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\AppBundle\EventListener;

use Proximum\Vimeet\Application\Adapter\MailerInterface;
use Proximum\Vimeet\Application\Event\ActivateAccountEvent;
use Proximum\Vimeet\Bundle\AppBundle\Mail\ActivateAccountMail;

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
            $event->getUser()->getEmail(),
            'VimeetAppBundle:Mail:activateAccount.html.twig',
            'activate_account',
            $event->getUser()->getLocale(),
            $event->getEvent(),
            $event->getActivateAccountToken()->getToken()
        );

        $this->mailer->send($mail);
    }
}
