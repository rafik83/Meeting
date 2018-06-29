<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Order;

use Proximum\Vimeet\Application\Command\Sheet\ChangeType\CancelPackage;
use Proximum\Vimeet\Application\Command\Sheet\ChangeType\CancelPackageHandler;
use Proximum\Vimeet\Domain\Cart\CartManager;
use Proximum\Vimeet\Domain\Exception\Order\OrderCanNotBeCancelledException;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Repository\OrderRepositoryInterface;

class CancelHandler
{
    /** @var OrderRepositoryInterface */
    private $orderRepository;

    /** @var CartManager */
    private $cartManager;

    /** @var CancelPackageHandler */
    private $cancelPackageHandler;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        CartManager $cartManager,
        CancelPackageHandler $cancelPackageHandler
    ) {
        $this->orderRepository = $orderRepository;
        $this->cartManager = $cartManager;
        $this->cancelPackageHandler = $cancelPackageHandler;
    }

    public function handle(Cancel $command): void
    {
        if ($command->order->hasInvoice()) {
            throw new OrderCanNotBeCancelledException(
                sprintf('Order %d has been invoiced', $command->order->getId())
            );
        }

        if ($command->order->isCancelled()) {
            throw new OrderCanNotBeCancelledException(
                sprintf('Order %d has already been cancelled', $command->order->getId())
            );
        }

        if ($command->order->hasType(Product::TYPE_PLAN)) {
            $this->cancelPackageHandler->handle(new CancelPackage($command->order->getSheet()));
        }

        $command->order->cancel();
        $this->orderRepository->set($command->order);

        $this->cartManager->emptyCart($command->order->getSheet());
    }
}
