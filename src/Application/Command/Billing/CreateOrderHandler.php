<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Billing;

use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Repository\OrderRepositoryInterface;

class CreateOrderHandler
{
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(OrderRepositoryInterface $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }

    public function handle(CreateOrder $createOrder)
    {
        $this->orderRepository->add(new Order(
            $createOrder->sheet,
            $createOrder->state,
            $createOrder->proFormaTemplate,
            $createOrder->packageData,
            $createOrder->billingData,
            $createOrder->createdAt,
            $createOrder->paymentMode
        ));
    }
}
