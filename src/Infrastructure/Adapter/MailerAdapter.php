<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Adapter;

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
     * @var TranslatorAdapter
     */
    private $translator;

    /**
     * @param \Swift_Mailer     $mailer
     * @param \Twig_Environment $twig
     * @param TranslatorAdapter $translator
     */
    public function __construct(\Swift_Mailer $mailer, \Twig_Environment $twig, TranslatorAdapter $translator)
    {
        $this->mailer     = $mailer;
        $this->twig       = $twig;
        $this->translator = $translator;
    }

    /**
     * Send Mail via Swift Mailer
     *
     * @param Mail $mail
     */
    public function send(Mail $mail)
    {
        /** @var \Twig_Template $template */
        $template = $this->twig->loadTemplate($mail->getTemplate());
        $body     = $template->render(['mail' => $mail]);
        $subject  = $this->translator->trans(
            $mail->getSubject(),
            $mail->getSubjectParameters(),
            'mail',
            $mail->getLocale()
        );

        $message = \Swift_Message::newInstance()
            ->setSubject($subject)
            ->setFrom($mail->getSender())
            ->setTo($mail->getReceiver());

        $emailTeam = $mail->getEvent()->getEmailTeam();

        if ($mail->sendToEmailTeam() && null !== $emailTeam) {
            $message->setBcc($emailTeam);
        }
        $message
            ->setBody($body)
            ->setContentType('text/html');

        $message->getHeaders()->addTextHeader('X-Message-ID', $mail->getMessageId());

        $this->mailer->send($message);
    }
}
