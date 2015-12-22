<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Package;

use Proximum\Vimeet\Application\Exception\Package\BoughtParticipantAlreadyAddedException;
use Proximum\Vimeet\Application\Exception\Package\ForgotToAddQuantityException;
use Proximum\Vimeet\Domain\Repository\CartRepositoryInterface;

class AddProductsHandler
{
    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @param CartRepositoryInterface  $cartRepository
     */
    public function __construct(CartRepositoryInterface $cartRepository)
    {
        $this->cartRepository  = $cartRepository;
    }

    /**
     * @param AddProducts $addProducts
     *
     * @throws ForgotToAddQuantityException
     * @throws BoughtParticipantAlreadyAddedException
     */
    public function handle(AddProducts $addProducts)
    {
        foreach ($addProducts->cart->getData() as $stepKey => $step) {
            foreach ($step as $elementKey => $element) {
                if (isset($element['planning']) && $element['planning']) {
                    if (!isset($addProducts->packageData[$stepKey][$elementKey]['quantity'])) {
                        throw new ForgotToAddQuantityException();
                    }
                }
            }
        }

        $addProducts->cart->setData($addProducts->packageData);
        $this->cartRepository->set($addProducts->cart);
    }
}
