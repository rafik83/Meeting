<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\EventListener\Sheet;

use FOS\ElasticaBundle\Persister\ObjectPersister;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Order\OrderConfirmEvent;
use Proximum\Vimeet\Application\Event\Package\StepDoneEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SheetPopulateEventSubscriber implements EventSubscriberInterface
{
    /**
     * @var ObjectPersister
     */
    private $persister;

    /**
     * @param ObjectPersister $persister
     */
    public function __construct(ObjectPersister $persister)
    {
        $this->persister = $persister;
    }

    /**
     * @param StepDoneEvent $event
     */
    public function onPackageStep(StepDoneEvent $event)
    {
        $this->persister->replaceOne($event->getSheet());
    }

    /**
     * @param OrderConfirmEvent $event
     */
    public function onOrderConfirmed(OrderConfirmEvent $event)
    {
        $this->persister->replaceOne($event->getOrder()->getSheet());
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            Events::ORDER_CONFIRMED   => 'onOrderConfirmed',
            Events::PACKAGE_STEP_DONE => 'onPackageStep',
        ];
    }
}
