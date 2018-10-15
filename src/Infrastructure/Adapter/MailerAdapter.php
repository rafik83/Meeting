<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Adapter;

use Proximum\Vimeet\Application\Adapter\MailerInterface;
use Proximum\Vimeet\Application\Components\Mail\AbstractMail;
use Proximum\Vimeet\Application\Components\Mail\UserMail;
use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Sheet\AddParticipantMail;

class MailerAdapter implements MailerInterface
{
    /** @var \Swift_Mailer */
    private $mailer;

    /** @var \Twig_Environment */
    private $twig;

    /** @var TranslatorAdapter */
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
     * @param AbstractMail $mail
     */
    public function send(AbstractMail $mail)
    {
        /** @var \Twig_Template $template */
        $template = $this->twig->loadTemplate($mail->getTemplate());
        $body = $template->render(['mail' => $mail]);

        if ($mail instanceof AddParticipantMail) {
            dump($mail);die();
        }
        if ($mail->hasToTranslateSubject()) {
            $subject  = $this->translator->trans(
                $mail->getSubject(),
                $mail->getSubjectParameters(),
                'mail',
                $mail->getLocale()
            );
        } else {
            $subject = $mail->getSubject();
        }

        $message = new \Swift_Message($subject);
        $message->setFrom($mail->getSender());

        foreach ($mail->getReceivers() as $receiver) {
            $message->addTo($receiver);
        }

        if (true === $mail->sendToEmailTeam() && $mail instanceof UserMail) {
            $message->setBcc($mail->getEvent()->getEmailTeam());
        }

        $message
            ->setBody($body)
            ->setContentType('text/html');

        $message->getHeaders()->addTextHeader('X-Message-ID', $mail->getMessageId());

        $this->mailer->send($message);
    }

    protected function getMailer()
    {
        return $this->mailer;
    }
}
