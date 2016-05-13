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

class RemoveRowHandler
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
     * @param RemoveRow $removeRow
     *
     * @deprecated must be rewrited in order to remove a row from db
     */
    public function handle(RemoveRow $removeRow)
    {
        $this->orderRepository->set($removeRow->order);
    }
}
