<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\AppBundle\EventListener;

use Proximum\Vimeet\Application\Event\ActivateAccountEvent;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Translation\Translator;

class ActivateAccountEventListener
{
    /**
     * @var \Swift_Mailer
     */
    private $mailer;

    /**
     * @var EngineInterface
     */
    private $templating;

    /**
     * @var Translator
     */
    private $translator;

    /**
     * @var string
     */
    private $sender;


    /**
     * @param \Swift_Mailer   $mailer
     * @param EngineInterface $templating
     * @param string          $sender
     * @param Translator      $translator
     */
    public function __construct(\Swift_Mailer $mailer, EngineInterface $templating, Translator $translator, $sender)
    {
        $this->mailer     = $mailer;
        $this->templating = $templating;
        $this->translator = $translator;
        $this->sender     = $sender;
    }

    /**
     * @param ActivateAccountEvent $event
     */
    public function sendToken(ActivateAccountEvent $event)
    {
        $message = \Swift_Message::newInstance()
            ->setSubject($this->translator->trans('mail.activateAccount.subject', ['%event%' => $event->getEvent()->getTitle()], 'mail'))
            ->setFrom($this->sender)
            ->setTo($event->getUser()->getEmail())
            ->setBody(
                $this->templating->render(
                    'VimeetAppBundle:Mail:activateAccount.html.twig',
                    [
                        'token'  => $event->getActivateAccountToken()->getToken(),
                        'event'  => $event->getEvent(),
                        'locale' => $event->getLocale()
                    ]
                )
            )
            ->setContentType('text/html');

        $message->getHeaders()->addTextHeader('X-Message-ID', 'activate_account');

        $this->mailer->send($message);
    }
}
