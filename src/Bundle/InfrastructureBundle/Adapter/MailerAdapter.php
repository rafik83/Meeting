<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\InfrastructureBundle\Adapter;

use Proximum\Vimeet\Application\Adapter\MailerInterface;
use Proximum\Vimeet\Application\Components\Mail\Mail;

class MailerAdapter implements MailerInterface
{
    /**
     * @var \Swift_Mailer
     */
    private $mailer;

    /**
     * @var \Twig_Environment
     */
    private $twig;

    /**
     * @param \Swift_Mailer     $mailer
     * @param \Twig_Environment $twig
     */
    public function __construct(\Swift_Mailer $mailer, \Twig_Environment $twig)
    {
        $this->mailer = $mailer;
        $this->twig   = $twig;
    }

    public function send(Mail $mail)
    {
        $message = \Swift_Message::newInstance()
            ->setSubject($this->twig->loadTemplate($mail->getTemplate())->renderBlock('subject', [
                'mail' => $mail
            ]))
            ->setFrom($mail->getSender())
            ->setTo($mail->getReceiver())
            ->setBody($this->twig->loadTemplate($mail->getTemplate())->renderBlock('body', [
                'mail' => $mail
            ]))
            ->setContentType('text/html');

        $message->getHeaders()->addTextHeader('X-Message-ID', $mail->getMessageId());

        $this->mailer->send($message);
    }
}
