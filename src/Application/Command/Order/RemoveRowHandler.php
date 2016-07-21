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
use Proximum\Vimeet\Domain\Repository\OrderRowRepositoryInterface;

class RemoveRowHandler
{
    /**
     * @var OrderRowRepositoryInterface
     */
    private $orderRowRepository;

    /**
     * @var OrderRowRepositoryInterface
     */
    private $orderRepository;

    /**
     * AddRowHandler constructor.
     *
     * @param OrderRowRepositoryInterface $orderRowRepository
     */
    public function __construct(OrderRowRepositoryInterface $orderRowRepository, OrderRepositoryInterface $orderRepositoryInterface)
    {
        $this->orderRowRepository = $orderRowRepository;
        $this->orderRepository = $orderRepositoryInterface;
    }

    /**
     * @param RemoveRow $removeRow
     *
     * @throws \Exception
     */
    public function handle(RemoveRow $removeRow)
    {
        if ($removeRow->row->isProduct()) {
            throw new \Exception('Delete a product row is not allowed');
        }

        $removeRow->order->removeCustomRow($removeRow->row);
        $this->orderRepository->set($removeRow->order);
    }
}
