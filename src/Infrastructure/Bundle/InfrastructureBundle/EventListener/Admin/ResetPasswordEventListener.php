<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\EventListener\Admin;

use Proximum\Vimeet\Application\Adapter\MailerInterface;
use Proximum\Vimeet\Application\Event\Admin\ResetPasswordEvent;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Mail\Admin\ResetPasswordMail;

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
            $event->getAdmin()->getEmail(),
            'VimeetAppBundle:Mail:Admin/resetPassword.html.twig',
            'admin_forgot_password',
            $event->getLocale(),
            $event->getForgottenPasswordToken()->getToken()
        );

        $this->mailer->send($mail);
    }
}
