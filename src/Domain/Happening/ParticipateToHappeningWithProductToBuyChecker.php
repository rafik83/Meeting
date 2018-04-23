<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Happening;

use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\Package;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Repository\ProductAttributedToParticipantRepositoryInterface;

class ParticipateToHappeningWithProductToBuyChecker
{
    /** @var ProductAttributedToParticipantRepositoryInterface */
    private $productAttributedToParticipantRepository;

    public function __construct(
        ProductAttributedToParticipantRepositoryInterface $productAttributedToParticipantRepository
    ) {
        $this->productAttributedToParticipantRepository = $productAttributedToParticipantRepository;
    }

    public function canParticipate(Participant $participant, Happening $toHappening): bool
    {
        if (!$toHappening->hasProducts()) {
            return true;
        }

        if (!$participant->getSheet()->getPackage()->isPassable()) {
            return true;
        }

        if (!$this->packageHasAtLeastOneProductNeededByHappening(
            $participant->getSheet()->getPackage(),
            $toHappening
        )) {
            return true;
        }

        return $this->productAttributedToParticipantRepository->participantHasAtLeastOneProduct(
            $participant,
            $toHappening->getProducts()
        );
    }

    private function packageHasAtLeastOneProductNeededByHappening(Package $package, Happening $happening): bool
    {
        $packageOptionsIndexedIds = [];

        foreach ($package->getOptions() as $option) {
            $packageOptionsIndexedIds[$option->getId()] = true;
        }

        foreach ($happening->getProducts() as $product) {
            if (isset($packageOptionsIndexedIds[$product->getId()])) {
                return true;
            }
        }

        return false;
    }
}
