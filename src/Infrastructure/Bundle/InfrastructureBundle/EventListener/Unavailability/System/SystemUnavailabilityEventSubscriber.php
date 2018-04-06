<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\EventListener\Unavailability\System;

use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Participant\ParticipantRemovedEvent;
use Proximum\Vimeet\Application\Event\Participant\ProductSetOnParticipantEvent;
use Proximum\Vimeet\Domain\Unavailability\SystemGenerator\Generator;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SystemUnavailabilityEventSubscriber implements EventSubscriberInterface
{
    /** @var Generator */
    private $generator;

    public function __construct(Generator $generator)
    {
        $this->generator = $generator;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            Events::PARTICIPANT_PRODUCT_SET => 'onParticipantProductSet',
            Events::PARTICIPANT_REMOVED => 'onParticipantRemoved',
        ];
    }

    public function onParticipantProductSet(ProductSetOnParticipantEvent $event): void
    {
        $this->generator->generateSystemUnavailability(
            $event->participant->getSheet()->getEvent(),
            $event->participant->getUser()
        );
    }

    public function onParticipantRemoved(ParticipantRemovedEvent $event): void
    {
        $domainEvent = $event->sheet->getEvent();

        foreach ($event->usersRemovedFromSheet as $user) {
            $this->generator->generateSystemUnavailability(
                $domainEvent,
                $user
            );
        }
    }
}
