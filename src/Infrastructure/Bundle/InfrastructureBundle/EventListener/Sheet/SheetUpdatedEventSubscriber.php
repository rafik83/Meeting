<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\EventListener\Sheet;

use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Sheet\SheetUpdatedEvent;
use Proximum\Vimeet\Domain\Sheet\CompletenessCalculator;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SheetUpdatedEventSubscriber implements EventSubscriberInterface
{
    /**
     * @var CompletenessCalculator
     */
    private $completenessCalculator;

    /**
     * @param CompletenessCalculator $completenessCalculator
     */
    public function __construct(CompletenessCalculator $completenessCalculator)
    {
        $this->completenessCalculator = $completenessCalculator;
    }

    /**
     * @param SheetUpdatedEvent $sheetUpdatedEvent
     */
    public function onSheetUpdated(SheetUpdatedEvent $sheetUpdatedEvent)
    {
        $this->completenessCalculator->calculateCompleteness($sheetUpdatedEvent->getSheet());
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            Events::SHEET_UPDATED => 'onSheetUpdated',
        ];
    }
}
