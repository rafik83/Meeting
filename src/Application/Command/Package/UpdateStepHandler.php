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
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class UpdateStepHandler
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
     * @param UpdateStep $updateStep
     *
     * @throws ForgotToAddQuantityException
     * @throws BoughtParticipantAlreadyAddedException
     */
    public function handle(UpdateStep $updateStep)
    {
        $packageData = $updateStep->cart->getData();

        $packageData[$updateStep->step] = $updateStep->packageData;

        $updateStep->cart->setData($packageData);

        $this->cartRepository->set($updateStep->cart);
    }
}
