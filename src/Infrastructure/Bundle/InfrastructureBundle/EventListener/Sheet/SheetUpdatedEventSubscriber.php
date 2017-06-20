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
use Proximum\Vimeet\Application\Event\Sheet\SheetChangedTypeEvent;
use Proximum\Vimeet\Application\Event\Sheet\SheetTitleCheckEvent;
use Proximum\Vimeet\Application\Event\Sheet\SheetUpdatedEvent;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Sheet\CompletenessCalculator;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SheetUpdatedEventSubscriber implements EventSubscriberInterface
{
    /**
     * @var CompletenessCalculator
     */
    private $completenessCalculator;

    /**
     * @var SheetRepositoryInterface
     */
    private $sheetRepository;

    /**
     * @param CompletenessCalculator   $completenessCalculator
     * @param SheetRepositoryInterface $sheetRepository
     */
    public function __construct(
        CompletenessCalculator $completenessCalculator,
        SheetRepositoryInterface $sheetRepository
    ) {
        $this->completenessCalculator = $completenessCalculator;
        $this->sheetRepository        = $sheetRepository;
    }

    /**
     * @param SheetUpdatedEvent $sheetUpdatedEvent
     */
    public function onSheetUpdated(SheetUpdatedEvent $sheetUpdatedEvent)
    {
        $this->completenessCalculator->calculateCompleteness($sheetUpdatedEvent->getSheet());
    }

    /**
     * @param SheetChangedTypeEvent $sheetChangedTypeEvent
     */
    public function onChangeType(SheetChangedTypeEvent $sheetChangedTypeEvent)
    {
        $this->completenessCalculator->calculateCompleteness($sheetChangedTypeEvent->getSheet());
    }

    /**
     * @param SheetTitleCheckEvent $sheetTitleCheckEvent
     */
    public function onSheetTitleCheck(SheetTitleCheckEvent $sheetTitleCheckEvent)
    {
        if (empty($sheetTitleCheckEvent->getSheet()->getTitle())) {
            $sheet = $sheetTitleCheckEvent->getSheet();
            $sheet->setTitle($sheet->getOwner()->getFullname());

            $this->sheetRepository->set($sheet);
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            Events::SHEET_UPDATED      => 'onSheetUpdated',
            Events::SHEET_CHANGED_TYPE => 'onChangeType',
            Events::SHEET_TITLE_CHECK  => 'onSheetTitleCheck',
        ];
    }
}
