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
use Proximum\Vimeet\Domain\Repository\CartRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\OrderRepositoryInterface;

class CreateOrderHandler
{
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @param OrderRepositoryInterface $orderRepository
     * @param CartRepositoryInterface  $cartRepository
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        CartRepositoryInterface $cartRepository
    ) {
        $this->orderRepository = $orderRepository;
        $this->cartRepository  = $cartRepository;
    }

    /**
     * @param CreateOrder $createOrder
     */
    public function handle(CreateOrder $createOrder)
    {
        $this->orderRepository->add(new Order(
            $createOrder->sheet,
            $createOrder->state,
            $createOrder->proFormaTemplate,
            $createOrder->packageData,
            $createOrder->packageTemplate,
            $createOrder->billingData,
            $createOrder->billingTemplate,
            $createOrder->createdAt,
            $createOrder->paymentMode
        ));

        $this->cartRepository->delete($createOrder->cart);
    }
}
