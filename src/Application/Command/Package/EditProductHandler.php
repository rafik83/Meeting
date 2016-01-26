<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Package;

use Proximum\Vimeet\Application\Command\Order\AddNegative;
use Proximum\Vimeet\Application\Command\Order\AddNegativeHandler;
use Proximum\Vimeet\Application\Components\Order\OrderManager;
use Proximum\Vimeet\Domain\Repository\CartRepositoryInterface;

class EditProductHandler
{
    /**
     * @var OrderManager
     */
    private $orderManager;

    /**
     * @var AddNegativeHandler
     */
    private $orderAddNegativeHandler;

    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @param AddNegativeHandler      $orderAddNegativeHandler
     * @param CartRepositoryInterface $cartRepository
     * @param OrderManager            $orderManager
     */
    public function __construct(
        AddNegativeHandler $orderAddNegativeHandler,
        CartRepositoryInterface $cartRepository,
        OrderManager $orderManager
    ) {
        $this->orderAddNegativeHandler = $orderAddNegativeHandler;
        $this->cartRepository          = $cartRepository;
        $this->orderManager            = $orderManager;
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
        $createOrderAddNegative = new AddNegative(
            $editProduct->sheet,
            $this->getNewPackageData($editProduct),
            new \DateTime()
        );

        $this->orderAddNegativeHandler->handle($createOrderAddNegative);
    }

    /**
     * @param EditProduct $editProduct
     */
    private function addProductToCart(EditProduct $editProduct)
    {
        $data = $this->orderManager->mergeTwoPackageData(
            $this->getNewPackageData($editProduct),
            $editProduct->cart->getData()
        );
        $editProduct->cart->setData($data);
        $this->cartRepository->set($editProduct->cart);
    }

    /**
     * @param EditProduct $editProduct
     *
     * @return array
     */
    private function getNewPackageData(EditProduct $editProduct)
    {
        return [
            $editProduct->product->getStep()->getKey() => [
                $editProduct->product->getKey() => [
                    'value' => true,
                    'quantity' => $editProduct->getNewQuantity(),
                ]
            ]
        ];
    }
}
