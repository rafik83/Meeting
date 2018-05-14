<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\EventListener\Happening;

use Proximum\Vimeet\Application\Adapter\MailerInterface;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Happening\HappeningParticipationAutomaticallyUpdatedEvent;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Happening\HappeningParticipationAutomaticallyUpdatedMail;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class HappeningParticipationEventSubscriber implements EventSubscriberInterface
{
    /** @var MailerInterface */
    private $mailer;

    /** @var RequestStack */
    private $requestStack;

    /** @var string */
    private $sender;

    public function __construct(
        MailerInterface $mailer,
        RequestStack $requestStack,
        string $sender
    ) {
        $this->mailer = $mailer;
        $this->requestStack = $requestStack;
        $this->sender = $sender;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            Events::HAPPENING_PARTICIPATION_AUTOMATICALLY_UPDATED => 'onParticipationAutomaticallyUpdated',
        ];
    }

    public function onParticipationAutomaticallyUpdated(HappeningParticipationAutomaticallyUpdatedEvent $event): void
    {
        $request = $this->requestStack->getCurrentRequest();
        if (!$request instanceof Request) {
            return;
        }

        $receivers = [];
        foreach ($event->happeningParticipationViews as $happeningParticipationView) {
            /** @var Participant $addedParticipant */
            foreach ($addedParticipant as $happeningParticipationView->addedParticipants) {
                $receivers[] = $addedParticipant->getEmail();
            }

            /** @var Participant $removedParticipant */
            foreach ($removedParticipant as $happeningParticipationView->addedParticipants) {
                $receivers[] = $removedParticipant->getEmail();
            }
        }

        $this->mailer->send(
            new HappeningParticipationAutomaticallyUpdatedMail(
                $event->happeningParticipationViews,
                $this->sender,
                $receivers,
                $request->getLocale()
            )
        );
    }
}
