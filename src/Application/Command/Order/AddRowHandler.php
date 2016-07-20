<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Order;

use Proximum\Vimeet\Domain\Model\Order\CustomRow;
use Proximum\Vimeet\Domain\Model\Order\Row;
use Proximum\Vimeet\Domain\Repository\OrderRepositoryInterface;

class AddRowHandler
{
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * AddRowHandler constructor.
     *
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(OrderRepositoryInterface $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }

    /**
     * @param AddRow $addRow
     *
     * @deprecated must be rewrited in order to insert a row in db
     */
    public function handle(AddRow $addRow)
    {
        $customRow = Row::createCustomRow(
            $addRow->order,
            $addRow->quantity,
            $addRow->groupId,
            $addRow->label,
            $addRow->description,
            $addRow->price
        );

        $addRow->order->addCustomRow($customRow);
        $this->orderRepository->set($addRow->order);
    }
}
