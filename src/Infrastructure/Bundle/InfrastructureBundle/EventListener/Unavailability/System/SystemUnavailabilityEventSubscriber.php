<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\EventListener\Unavailability\System;

use Proximum\Vimeet\Application\Event\Cart\ParticipantCartRowAddedEvent;
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
            Events::PARTICIPANT_CART_ROW_ADDED => 'onParticipantCartRowAdded',
        ];
    }

    public function onParticipantCartRowAdded(ParticipantCartRowAddedEvent $participantCartRowAddedEvent): void
    {
        // We generate the system unavailability only when the participant added a product to the cart
        // Without a product already set on him/her and the previous product in the cart is different
        if (false === $participantCartRowAddedEvent->hadAlreadyThisProductInCart
            && false === $participantCartRowAddedEvent->hadPreviousProduct
        ) {
            $this->generator->generateSystemUnavailability(
                $participantCartRowAddedEvent->participant->getSheet()->getEvent(),
                $participantCartRowAddedEvent->participant->getUser()
            );
        }
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
