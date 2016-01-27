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

class EditProductHandler
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
     * @param EditProduct $editProduct
     */
    public function handle(EditProduct $editProduct)
    {
        if ($editProduct->isNegative()) {
            $this->createNegativeOrder($editProduct);
        } elseif ($editProduct->isPositive()) {
            $this->addProductToCart($editProduct);
        }
    }

    /**
     * @param EditProduct $editProduct
     */
    private function createNegativeOrder(EditProduct $editProduct)
    {
        $order = new Order(
            $editProduct->sheet,
            Order::STATE_PAID,
            [],
            $editProduct->sheet->getTypePackageTemplate(),
            $editProduct->sheet->getBillingData(),
            $editProduct->sheet->getEvent()->getBillingTemplate(),
            $editProduct->createdAt,
            PaymentMode::NOPAYMENT
        );

        $order->addRow(
            $editProduct->product->getStep()->getKey(),
            $editProduct->product->getKey(),
            $editProduct->product->getType(),
            $editProduct->product->getLabel($editProduct->locale),
            $editProduct->product->getDescription($editProduct->locale),
            $editProduct->product->getUnitPrice(),
            $editProduct->getNewQuantity()
        );

        $this->orderRepository->add($order);

        $sheetData = $this->orderManager->mergeTwoPackageData(
            $editProduct->sheet->getPackageData(),
            $order->getPackageData()
        );

        $editProduct->sheet->setPackageData($sheetData);
        $this->sheetRepository->set($editProduct->sheet);
    }

    /**
     * @param EditProduct $editProduct
     */
    private function addProductToCart(EditProduct $editProduct)
    {
        $editProduct->cart->setRow(
            $editProduct->product->getStep()->getKey(),
            $editProduct->product->getKey(),
            $editProduct->getNewQuantity()
        );

        $this->cartRepository->set($editProduct->cart);
    }
}
