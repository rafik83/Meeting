<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\EventListener\Happening;

use Proximum\Vimeet\Application\Components\Happening\Participation\DisableEnableParticipation;
use Proximum\Vimeet\Application\Components\Happening\Participation\UserParticipantAvailabilityReAggregator;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Happening\DatesUpdated;
use Proximum\Vimeet\Application\Event\Happening\TypesUpdated;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class HappeningEventSubscriber implements EventSubscriberInterface
{
    /** @var DisableEnableParticipation */
    private $disableEnableParticipation;

    /** @var UserParticipantAvailabilityReAggregator */
    private $participantAvailabilityReAggregator;

    /**
     * @param DisableEnableParticipation              $disableEnableParticipation
     * @param UserParticipantAvailabilityReAggregator $participantAvailabilityReAggregator
     */
    public function __construct(
        DisableEnableParticipation $disableEnableParticipation,
        UserParticipantAvailabilityReAggregator $participantAvailabilityReAggregator
    ) {
        $this->disableEnableParticipation = $disableEnableParticipation;
        $this->participantAvailabilityReAggregator = $participantAvailabilityReAggregator;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            Events::HAPPENING_TYPES_UPDATED => 'onTypesUpdated',
            Events::HAPPENING_DATES_UPDATED => 'onDatesUpdated',
        ];
    }

    /**
     * @param TypesUpdated $event
     */
    public function onTypesUpdated(TypesUpdated $event)
    {
        $this->disableEnableParticipation->resolveParticipations($event->getHappening());
    }

    /**
     * @param DatesUpdated $event
     */
    public function onDatesUpdated(DatesUpdated $event)
    {
        $this->participantAvailabilityReAggregator->recalculateAvailabilityAggregator($event->getHappening());
    }
}
