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
use Proximum\Vimeet\Application\Components\Mail\AbstractCustomizedMail;
use Proximum\Vimeet\Application\Components\Mail\AbstractMail;
use Proximum\Vimeet\Application\Components\Mail\UserMail;
use Psr\Log\LoggerInterface;

class MailerAdapter implements MailerInterface
{
    /** @var \Swift_Mailer */
    private $mailer;

    /** @var \Twig_Environment */
    private $twig;

    /** @var TranslatorAdapter */
    private $translator;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(
        \Swift_Mailer $mailer,
        \Twig_Environment $twig,
        TranslatorAdapter $translator,
        LoggerInterface $logger
    ) {
        $this->mailer = $mailer;
        $this->twig = $twig;
        $this->translator = $translator;
        $this->logger = $logger;
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

        if (true === $mail->sendToEmailTeam()
            && ($mail instanceof UserMail || $mail instanceof AbstractCustomizedMail)
        ) {
            $message->setBcc($mail->getEvent()->getEmailTeam());
        }

        $message
            ->setBody($body)
            ->setContentType('text/html');

        $message->getHeaders()->addTextHeader('X-Message-ID', $mail->getMessageId());

        $failedRecipients = [];
        $this->mailer->send($message, $failedRecipients);

        $context = ['subject' => $subject, 'messageId' => $mail->getMessageId()];

        foreach ($mail->getReceivers() as $receiver) {
            if (in_array($receiver, $failedRecipients, true)) {
                $this->logger->error(
                    sprintf('Failed to send email to %s', $receiver),
                    $context + ['to' => $receiver]
                );

                continue;
            }

            $this->logger->info(
                sprintf('Email sent to %s', $receiver),
                $context + ['to' => $receiver]
            );
        }
    }

    protected function getMailer()
    {
        return $this->mailer;
    }
}
