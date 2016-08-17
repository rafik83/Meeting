<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Order;

use Proximum\Vimeet\Domain\Model\Order\Row;
use Proximum\Vimeet\Domain\Repository\OrderRepositoryInterface;

class AddRowToGroupHandler
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
     * @param AddRowToGroup $addRow
     */
    public function handle(AddRowToGroup $addRow)
    {
        $customRow = Row::createCustomRowToGroup(
            $addRow->order,
            $addRow->quantity,
            $addRow->groupId,
            $addRow->label,
            $addRow->price
        );

        $addRow->order->addCustomRow($customRow);
        $this->orderRepository->set($addRow->order);
    }
}

