<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\AppBundle\EventListener;


use Proximum\Vimeet\Application\Event\ResetPasswordEvent;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\EventDispatcher\Event;

class ResetPasswordEventListener
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
     * @param \Swift_Mailer    $mailer
     * @param EngineInterface $templating
     */
    public function __construct(\Swift_Mailer $mailer, EngineInterface $templating)
    {
        $this->mailer     = $mailer;
        $this->templating = $templating;
    }

    /**
     * @param Event $resetPasswordEvent
     */
    public function sendToken(Event $resetPasswordEvent)
    {
        $message = \Swift_Message::newInstance()
            ->setSubject(sprintf('[%s] Reset password', $resetPasswordEvent->getApplicationEvent()->getEventView()->title))
            ->setFrom('vimeet@proximumgroup.com')
            ->setTo($resetPasswordEvent->getApplicationEvent()->getUser()->getEmail())
            ->setBody(
                $this->templating->render(
                    'VimeetAppBundle:Mail:resetPassword.html.twig',
                    [
                        'token'     => $resetPasswordEvent->getApplicationEvent()->getForgottenPasswordToken()->getToken(),
                        'eventView' => $resetPasswordEvent->getApplicationEvent()->getEventView(),
                    ]
                )
            )
            ->setContentType('text/html');

        $this->mailer->send($message);
    }
}
