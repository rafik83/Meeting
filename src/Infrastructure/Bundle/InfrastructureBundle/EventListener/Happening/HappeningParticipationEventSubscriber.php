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
use Proximum\Vimeet\Application\Event\Happening\ParticipateHappeningEvent;
use Proximum\Vimeet\Application\Event\Happening\UnParticipateHappeningEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;

/**
 * @todo:
 * - Stack all participate and unparticipate events : store update by Happening?
 *        Happening1:
 *           - Michel is unregistered
 *           - Henry is registered
 *        Happening2:
 *           - Henry is unregistered
 * - Listen to Events::HAPPENING_PARTICIPATED in order to run $this->flashBag->add('success', $message) with one unique message
 */
class HappeningParticipationEventSubscriber implements EventSubscriberInterface
{
    /** @var FlashBagInterface */
    private $flashBag;

    public function __construct(FlashBagInterface $flashBag)
    {
        $this->flashBag = $flashBag;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            Events::HAPPENING_PARTICIPATE => 'onParticipateToHappening',
            Events::HAPPENING_UN_PARTICIPATE => 'onUnparticipateToHappening',
        ];
    }

    public function onParticipateToHappening(ParticipateHappeningEvent $participateHappeningEvent): void
    {
        if (!$participateHappeningEvent->automaticallyThroughProductAttribution) {
            return;
        }

        $this->flashBag->add(
            'success',
            $participateHappeningEvent->participant->getFullname(). ' added to ' . $participateHappeningEvent->happening->getTitle('fr') // need to get the locale from the Request
        );
    }

    public function onUnparticipateToHappening(UnParticipateHappeningEvent $unParticipateHappeningEvent): void
    {
        if (!$unParticipateHappeningEvent->automaticallyThroughProductAttribution) {
            return;
        }

        $this->flashBag->add(
            'success',
            $unParticipateHappeningEvent->participant->getFullname() . ' removed from ' . $unParticipateHappeningEvent->happening->getTitle('fr') // need to get the locale from the Request
        );
    }
}
