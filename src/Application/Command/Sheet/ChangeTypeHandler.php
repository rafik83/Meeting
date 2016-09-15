<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Sheet;

use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Repository\OrderRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class ChangeTypeHandler
{
    /**
     * @var SheetRepositoryInterface
     */
    private $sheetRepository;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @param SheetRepositoryInterface $sheetRepository
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(SheetRepositoryInterface $sheetRepository, OrderRepositoryInterface $orderRepository)
    {
        $this->sheetRepository = $sheetRepository;
        $this->orderRepository = $orderRepository;
    }

    /**
     * @param ChangeType $changeType
     */
    public function handle(ChangeType $changeType)
    {
        if (null === $changeType->type || $changeType->type === $changeType->sheet->getType()) {
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
            array_map(
                function (Order $order) {
                    $order->setCancelled();
                    $this->orderRepository->set($order);
                },
                $this->orderRepository->findBySheet($changeType->sheet)
            );
        }
    }
}
