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

        foreach ($updateStep->packageData as $elementKey => $element) {
            if (isset($element['participant'])) {
                if ($element['participant']) {
                    if (!isset($updateStep->packageData[$elementKey]['quantity'])) {
                        throw new ForgotToAddQuantityException();
                    }

                    if (!isset($packageData[$updateStep->step][$elementKey]['quantity'])) {
                        $packageData[$updateStep->step][$elementKey]['quantity'] = 0;
                    }

                    $boughtAndAddedParticipant = count($updateStep->sheet->getParticipants()) - $updateStep->sheet->getType()->getFreeParticipant();
                    if ($updateStep->packageData[$elementKey]['quantity'] < $boughtAndAddedParticipant) {
                        throw new BoughtParticipantAlreadyAddedException();
                    }
                } else {
                    if (count($updateStep->sheet->getParticipants()) > $updateStep->sheet->getType()->getFreeParticipant()) {
                        throw new BoughtParticipantAlreadyAddedException();
                    }
                }
            } elseif (isset($element['planning']) && $element['planning']) {
                if (!isset($updateStep->packageData[$elementKey]['quantity'])) {
                    throw new ForgotToAddQuantityException();
                }
            }
        }

        $packageData[$updateStep->step] = $updateStep->packageData;

        $updateStep->cart->setData($packageData);

        $this->cartRepository->set($updateStep->cart);
    }
}
