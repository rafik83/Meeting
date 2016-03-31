<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\EventListener;

use Proximum\Vimeet\Application\Adapter\MailerInterface;
use Proximum\Vimeet\Application\Event\User\ChangeMailAddressEvent;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Mail\ChangeNewMailAddressMail;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Mail\ChangeOldMailAddressMail;

class ChangeMailAddressEventListener
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
     * @param ChangeMailAddressEvent $event
     */
    public function sendToken(ChangeMailAddressEvent $event)
    {
        $oldMail = new ChangeOldMailAddressMail(
            $this->sender,
            $event->getUser()->getEmail(),
            'VimeetAppBundle:Mail:ChangeMail/oldMail.html.twig',
            'change_mail_old',
            $event->getUser()->getLocale(),
            $event->getChangeMailToken()->getMail()
        );

        $newMail = new ChangeNewMailAddressMail(
            $this->sender,
            $event->getChangeMailToken()->getMail(),
            'VimeetAppBundle:Mail:ChangeMail/newMail.html.twig',
            'change_mail_new',
            $event->getUser()->getLocale(),
            $event->getChangeMailToken()->getToken()
        );

        $this->mailer->send($oldMail);
        $this->mailer->send($newMail);
    }
}
