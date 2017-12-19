<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\EventListener\Happening;

use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Happening\TypesUpdated;
use Proximum\Vimeet\Domain\Happening\Participation\DisableEnableParticipation;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class HappeningEventSubscriber implements EventSubscriberInterface
{
    /** @var DisableEnableParticipation */
    private $disableEnableParticipation;

    /**
     * @param DisableEnableParticipation $disableEnableParticipation
     */
    public function __construct(DisableEnableParticipation $disableEnableParticipation)
    {
        $this->disableEnableParticipation = $disableEnableParticipation;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            Events::HAPPENING_TYPES_UPDATED => 'onTypesUpdated',
        ];
    }

    /**
     * @param TypesUpdated $event
     */
    public function onTypesUpdated(TypesUpdated $event)
    {
        $this->disableEnableParticipation->resolveParticipations($event->getHappening());
    }
}
