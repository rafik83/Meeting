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
use Proximum\Vimeet\Application\Event\ResetPasswordEvent;
use Proximum\Vimeet\Bundle\AppBundle\Mail\ResetPasswordMail;

class ResetPasswordEventListener
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
     * @param ResetPasswordEvent $event
     */
    public function sendToken(ResetPasswordEvent $event)
    {
        $mail = new ResetPasswordMail(
            $this->sender,
            $event->getUser()->getEmail(),
            'VimeetAppBundle:Mail:resetPassword.html.twig',
            'forgot_password',
            $event->getUser()->getLocale(),
            $event->getEventView()->title,
            $event->getForgottenPasswordToken()->getToken()
        );

        $this->mailer->send($mail);
    }
}
