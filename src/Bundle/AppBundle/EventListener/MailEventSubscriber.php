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
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Sheet\SheetValidatedEvent;
use Proximum\Vimeet\Bundle\AppBundle\Mail\SheetValidatedMail;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class MailEventSubscriber implements EventSubscriberInterface
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
     * MailEventSubscriber constructor.
     *
     * @param MailerInterface $mailer
     * @param string          $sender
     */
    public function __construct(MailerInterface $mailer, $sender)
    {
        $this->mailer = $mailer;
        $this->sender = $sender;
    }

    /**
     * @param SheetValidatedEvent $event
     */
    public function onSheetValidated(SheetValidatedEvent $event)
    {
        $owner = $event->getSheet()->getOwner()->getUser();

        $mail  = new SheetValidatedMail(
            $event->getSheet(),
            $this->sender,
            $owner->getEmail(),
            'VimeetAppBundle:Mail:Sheet/sheetValidated.html.twig',
            'sheet_validated',
            $owner->getLocale()
        );

        $this->mailer->send($mail);
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            Events::SHEET_VALIDATED => 'onSheetValidated',
        ];
    }
}
