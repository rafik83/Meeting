<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Sheet;

use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Sheet\SheetChangedTypeEvent;
use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Repository\OrderRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Adapter\DelayedEventDispatcher;

class ChangeTypeHandler
{
    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    /** @var OrderRepositoryInterface */
    private $orderRepository;

    /** @var DelayedEventDispatcher */
    private $eventDispatcher;

    /** @var TranslatorInterface */
    private $translator;

    /** @var \DateTimeInterface */
    private $datetime;

    /**
     * @param SheetRepositoryInterface $sheetRepository
     * @param OrderRepositoryInterface $orderRepository
     * @param TranslatorInterface      $translator
     * @param DelayedEventDispatcher   $eventDispatcher
     * @param \DateTimeInterface       $datetime
     */
    public function __construct(
        SheetRepositoryInterface $sheetRepository,
        OrderRepositoryInterface $orderRepository,
        TranslatorInterface $translator,
        DelayedEventDispatcher $eventDispatcher,
        \DateTimeInterface $datetime
    ) {
        $this->sheetRepository = $sheetRepository;
        $this->orderRepository = $orderRepository;
        $this->translator      = $translator;
        $this->eventDispatcher = $eventDispatcher;
        $this->datetime        = $datetime;
    }

    /**
     * @param ChangeType $changeType
     */
    public function handle(ChangeType $changeType)
    {
        $previousType = $changeType->sheet->getType();

        if (null === $changeType->type || $changeType->type === $previousType) {
            return;
        }

        // get previous package
        $previousPackage = $changeType->sheet->getType()->getPackage();

        // update sheet type
        $changeType->sheet->updateType($changeType->type);
        $this->sheetRepository->set($changeType->sheet);

        // get current package
        $currentPackage = $changeType->type->getPackage();

        // if previous package different of new one, cancel orders
        if ($previousPackage !== $currentPackage) {
            $orders = $this->orderRepository->findBySheet($changeType->sheet);

            if (count($orders)) {
                array_map(
                    function (Order $order) {
                        $order->cancel();
                        $this->orderRepository->set($order);
                    },
                    $orders
                );
            }
        }

        // dispatch SHEET_CHANGED_TYPE event
        $this->eventDispatcher->dispatch(
            Events::SHEET_CHANGED_TYPE,
            new SheetChangedTypeEvent(
                $changeType->sheet,
                $changeType->admin,
                $this->datetime,
                $this->translator->trans('admin.sheet.trace.changed_type_comment', [
                    '%fromType%' => $previousType->getTitle($changeType->locale),
                    '%toType%'   => $changeType->type->getTitle($changeType->locale),
                ]),
                $previousType->getTitle($changeType->locale),
                $changeType->locale
            )
        );
    }
}
