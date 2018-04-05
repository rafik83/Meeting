<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Participant;

use Proximum\Vimeet\Application\Adapter\DelayedEventDispatcherInterface;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Participant\ProductSetOnParticipantEvent;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Product;

class ParticipantProductSetter
{
    /** @var DelayedEventDispatcherInterface */
    private $eventDispatcher;

    public function __construct(
        DelayedEventDispatcherInterface $eventDispatcher
    ) {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function setProductOnParticipant(Participant $participant, Product $product): void
    {
        $participant->setParticipantProduct($product);

        $event = new ProductSetOnParticipantEvent($participant);
        $this->eventDispatcher->dispatch(Events::PARTICIPANT_PRODUCT_SET, $event);
    }
}
