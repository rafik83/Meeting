<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\EventListener\Order;

use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Order\OrderConfirmEvent;
use Proximum\Vimeet\Domain\Happening\ParticipateToHappeningsByProduct;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class OrderEventSubscriber implements EventSubscriberInterface
{
    /** @var ParticipateToHappeningsByProduct */
    private $participateToHappeningsByProduct;

    public function __construct(ParticipateToHappeningsByProduct $participateToHappeningsByProduct)
    {
        $this->participateToHappeningsByProduct = $participateToHappeningsByProduct;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            Events::ORDER_CONFIRMED => 'onOrderConfirmed',
        ];
    }

    public function onOrderConfirmed(OrderConfirmEvent $orderConfirmEvent)
    {
        $this->participateToHappeningsByProduct->handle($orderConfirmEvent->getOrder()->getSheet());
    }
}
