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
use Proximum\Vimeet\Application\Exception\Package\BoughtParticipantAlreadyAddedException;
use Proximum\Vimeet\Application\Exception\Package\EmptyPackageException;
use Proximum\Vimeet\Application\Exception\Package\ForgotToAddQuantityException;
use Proximum\Vimeet\Domain\Repository\CartRepositoryInterface;

class AddProductsHandler
{
    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var OrderManager
     */
    private $orderManager;

    /**
     * @param CartRepositoryInterface $cartRepository
     * @param OrderManager            $orderManager
     */
    public function __construct(
        CartRepositoryInterface $cartRepository,
        OrderManager $orderManager
    ) {
        $this->cartRepository  = $cartRepository;
        $this->orderManager    = $orderManager;
    }

    /**
     * @param AddProducts $addProducts
     *
     * @throws ForgotToAddQuantityException
     * @throws BoughtParticipantAlreadyAddedException
     */
    public function handle(AddProducts $addProducts)
    {
        foreach ($addProducts->packageData as $stepKey => $step) {
            foreach ($step as $elementKey => $element) {
                if (isset($element['participant'])) {
                    if (true === $element['participant']) {
                        if (!isset($addProducts->packageData[$stepKey][$elementKey]['quantity'])) {
                            throw new ForgotToAddQuantityException();
                        }

                        if (count($addProducts->sheet->getParticipants()) > (
                                $addProducts->sheet->getType()->getFreeParticipant() + $this->getParticipantBought($addProducts)
                            )
                        ) {
                            throw new BoughtParticipantAlreadyAddedException();
                        }
                    } else {
                        if (count($addProducts->sheet->getParticipants()) > (
                                $addProducts->sheet->getType()->getFreeParticipant() + $this->getParticipantBought($addProducts)
                            )
                        ) {
                            throw new BoughtParticipantAlreadyAddedException();
                        }
                    }
                } elseif (isset($element['planning']) && $element['planning']) {
                    if (!isset($addProducts->packageData[$stepKey][$elementKey]['quantity'])) {
                        throw new ForgotToAddQuantityException();
                    }
                }
            }
        }

        if (empty($this->orderManager->cleanFalseOption($addProducts->packageData))) {
            throw new EmptyPackageException();
        }

        $addProducts->cart->setData($addProducts->packageData);
        $this->cartRepository->set($addProducts->cart);
    }

    /**
     * @param AddProducts $addProducts
     * @return int
     */
    private function getParticipantBought(AddProducts $addProducts)
    {
        $sheet  = $addProducts->sheet;
        $bought = 0;

        foreach ($sheet->getOrders() as $order) {
            $bought += $this->orderManager->getParticipantBoughtForOrder($order);
        }

        $bought += $this->getParticipantBoughtForPackageData($addProducts);

        return $bought;
    }

    /**
     * @param AddProducts $addProducts
     * @return int
     */
    public function getParticipantBoughtForPackageData(AddProducts $addProducts)
    {
        $cart        = $addProducts->cart;
        $packageData = $addProducts->packageData;

        if (empty($cart->getTemplate()) || empty($cart->getData())) {
            return 0;
        }

        foreach ($cart->getTemplate() as $blockKey => $block) {
            foreach ($block['template'] as $productKey => $product) {
                if (isset($product['type']) && 'lib_participant' === $product['type']) {
                    if (isset($packageData[$blockKey][$productKey]['participant'])
                        && true === $packageData[$blockKey][$productKey]['participant']
                        && isset($packageData[$blockKey][$productKey]['quantity'])
                    ) {
                        return $packageData[$blockKey][$productKey]['quantity'];
                    } else {
                        return 0;
                    }
                }
            }
        }

        return 0;
    }
}
