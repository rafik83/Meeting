<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Package;

use Proximum\Vimeet\Application\Components\Order\OrderManager;
use Proximum\Vimeet\Application\Components\Payment\PaymentMode;
use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Repository\CartRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\OrderRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class UpdateProductHandler
{
    /**
     * @var OrderManager
     */
    private $orderManager;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var SheetRepositoryInterface
     */
    private $sheetRepository;

    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @param CartRepositoryInterface  $cartRepository
     * @param OrderRepositoryInterface $orderRepository
     * @param SheetRepositoryInterface $sheetRepository
     * @param OrderManager             $orderManager
     */
    public function __construct(
        CartRepositoryInterface $cartRepository,
        OrderRepositoryInterface $orderRepository,
        SheetRepositoryInterface $sheetRepository,
        OrderManager $orderManager
    ) {
        $this->orderRepository = $orderRepository;
        $this->cartRepository  = $cartRepository;
        $this->sheetRepository = $sheetRepository;
        $this->orderManager    = $orderManager;
    }

    /**
     * @param UpdateProduct $updateProduct
     */
    public function handle(UpdateProduct $updateProduct)
    {
        if ($updateProduct->isNegative()) {
            $this->createNegativeOrder($updateProduct);
        } elseif ($updateProduct->isPositive()) {
            $this->addProductToCart($updateProduct);
        }
    }

    /**
     * @param UpdateProduct $updateProduct
     */
    private function createNegativeOrder(UpdateProduct $updateProduct)
    {
        $order = new Order(
            $updateProduct->sheet,
            Order::STATE_PAID,
            [],
            $updateProduct->sheet->getTypePackageTemplate(),
            $updateProduct->sheet->getBillingData(),
            $updateProduct->sheet->getEvent()->getBillingTemplate(),
            $updateProduct->createdAt,
            PaymentMode::NOPAYMENT
        );

        $order->addRow(
            $updateProduct->product->getStep()->getKey(),
            $updateProduct->product->getKey(),
            $updateProduct->product->getType(),
            $updateProduct->product->getLabel($updateProduct->locale),
            $updateProduct->product->getDescription($updateProduct->locale),
            $updateProduct->product->getUnitPrice(),
            $updateProduct->getNewQuantity()
        );

        $this->orderRepository->add($order);

        $sheetData = $this->orderManager->mergeTwoPackageData(
            $updateProduct->sheet->getPackageData(),
            $order->getPackageData()
        );

        $updateProduct->sheet->setPackageData($sheetData);
        $this->sheetRepository->set($updateProduct->sheet);
    }

    /**
     * @param UpdateProduct $updateProduct
     */
    private function addProductToCart(UpdateProduct $updateProduct)
    {
        $updateProduct->cart->setRow(
            $updateProduct->product->getStep()->getKey(),
            $updateProduct->product->getKey(),
            $updateProduct->getNewQuantity()
        );

        $this->cartRepository->set($updateProduct->cart);
    }
}
