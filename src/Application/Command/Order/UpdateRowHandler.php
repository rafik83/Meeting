<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Order;

use Proximum\Vimeet\Domain\Repository\OrderRepositoryInterface;

class UpdateRowHandler
{
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * UpdateRowHandler constructor.
     *
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(OrderRepositoryInterface $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }

    /**
     * @param UpdateRow $updateRow
     *
     * @deprecated must be rewrited in order to update a row in db
     */
    public function handle(UpdateRow $updateRow)
    {
        $updateRow->order->updateCustomRow($updateRow->row);
        $this->orderRepository->set($updateRow->order);
    }
}
